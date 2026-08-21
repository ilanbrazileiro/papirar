<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Alternative;
use App\Models\Question;
use App\Models\User;
use App\Models\UserSession;
use App\Notifications\VerifyEmailNotification;
use App\Support\PublicQuestionUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicQuestionController extends Controller
{
    public function show(Request $request, string $subjectSlug, int $question, string $questionSlug): View|RedirectResponse
    {
        $questionModel = $this->findPublicQuestion($question);

        $canonicalSubjectSlug = PublicQuestionUrl::subjectSlug($questionModel);
        $canonicalQuestionSlug = PublicQuestionUrl::questionSlug($questionModel);

        if ($subjectSlug !== $canonicalSubjectSlug || $questionSlug !== $canonicalQuestionSlug) {
            return redirect()->to(PublicQuestionUrl::url($questionModel), 301);
        }

        $result = session('public_question_result');
        $showResult = is_array($result) && (int)($result['question_id'] ?? 0) === $questionModel->id;
        $selectedAlternativeId = $showResult ? (int)($result['selected_alternative_id'] ?? 0) : null;

        $alternatives = $questionModel->alternatives->map(function (Alternative $alternative) use ($showResult, $selectedAlternativeId) {
            $item = [
                'id' => $alternative->id,
                'letter' => $alternative->letter,
                'text' => $alternative->text,
                'is_selected' => $selectedAlternativeId === $alternative->id,
            ];

            if ($showResult) {
                $item['is_correct'] = (bool)$alternative->is_correct;
            }

            return $item;
        })->values();

        $plainStatement = $this->plainText($questionModel->statement);
        $subjectName = $questionModel->subject?->name ?: 'Concursos';
        $topicName = $questionModel->topic?->name;

        $seoTitle = Str::limit(
            'Questão' .
            ($questionModel->examBoard?->name ? ' ' . $questionModel->examBoard->name : '') .
            ' de ' . $subjectName .
            ($topicName ? ' - ' . $topicName : '') .
            ' | Papirar',
            65,
            ''
        );

        $seoDescription = Str::limit(
            'Resolva esta questão de ' . $subjectName .
            ($topicName ? ' sobre ' . $topicName : '') .
            ' no Papirar. ' . $plainStatement,
            158,
            ''
        );

        $relatedQuestions = Question::query()
            ->visibleToStudent()
            ->whereKeyNot($questionModel->id)
            ->when(
                $questionModel->topic_id,
                fn ($query) => $query->where('topic_id', $questionModel->topic_id),
                fn ($query) => $query->where('subject_id', $questionModel->subject_id)
            )
            ->with(['subject:id,name,slug', 'topic:id,name,slug'])
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (Question $related) => [
                'id' => $related->id,
                'url' => PublicQuestionUrl::url($related),
                'subject' => $related->subject?->name,
                'topic' => $related->topic?->name,
            ]);

        return view('site.questions.show', [
            'question' => $questionModel,
            'alternatives' => $alternatives,
            'canonicalUrl' => PublicQuestionUrl::url($questionModel),
            'seoTitle' => $seoTitle,
            'seoDescription' => $seoDescription,
            'showResult' => $showResult,
            'answerWasCorrect' => $showResult ? (bool)($result['is_correct'] ?? false) : null,
            'relatedQuestions' => $relatedQuestions,
        ]);
    }

    public function registerModal(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required','string','min:3','max:150'],
            'email' => ['required','email','max:190','unique:users,email'],
            'password' => ['required','confirmed','min:6'],
            'seguranca' => ['nullable','max:0'],
        ]);

        $user = DB::transaction(fn () => User::query()->create([
            'name' => trim($data['name']),
            'email' => mb_strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'role' => 'student',
            'is_active' => 1,
            'email_verified_at' => null,
        ]));

        $user->notify(new VerifyEmailNotification());
        Auth::login($user);
        $request->session()->regenerate();
        $this->registerSession($request, $user);

        return response()->json(['success' => true, 'authenticated' => true]);
    }

    public function loginModal(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        if (!Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => 1,
        ], $request->boolean('remember'))) {
            return response()->json([
                'message' => 'Login ou senha inválidos.',
                'errors' => ['email' => ['Login ou senha inválidos.']],
            ], 422);
        }

        $request->session()->regenerate();
        /** @var User $user */
        $user = Auth::user();

        if ($user->isAdmin()) {
            Auth::logout();

            return response()->json([
                'message' => 'Use o login administrativo para acessar sua conta.'
            ], 403);
        }

        $this->registerSession($request, $user);

        return response()->json(['success' => true, 'authenticated' => true]);
    }

    public function answer(Request $request, string $subjectSlug, int $question, string $questionSlug): RedirectResponse
    {
        $questionModel = $this->findPublicQuestion($question);

        $validated = $request->validate([
            'alternative_id' => ['required','integer'],
        ]);

        $selected = $questionModel->alternatives->firstWhere('id', (int)$validated['alternative_id']);

        if (!$selected) {
            return back()->withErrors([
                'alternative_id' => 'A alternativa selecionada não pertence a esta questão.'
            ]);
        }

        session()->flash('public_question_result', [
            'question_id' => $questionModel->id,
            'selected_alternative_id' => $selected->id,
            'is_correct' => (bool)$selected->is_correct,
        ]);

        return redirect()->to(PublicQuestionUrl::url($questionModel));
    }

    private function registerSession(Request $request, User $user): void
    {
        $sessionToken = Str::random(80);

        UserSession::updateOrCreate(
            ['user_id' => $user->id],
            [
                'session_token' => hash('sha256', $sessionToken),
                'ip_address' => (string)$request->ip(),
                'user_agent' => Str::limit((string)$request->userAgent(), 500, ''),
                'last_activity_at' => now(),
            ]
        );

        $user->forceFill([
            'last_login_at' => now(),
            'force_logout_at' => null,
        ])->save();

        $request->session()->put('auth_session_token', $sessionToken);
    }

    private function findPublicQuestion(int $id): Question
    {
        return Question::query()
            ->visibleToStudent()
            ->with([
                'alternatives',
                'subject:id,name,slug',
                'topic:id,subject_id,name,slug',
                'corporation:id,name',
                'exam:id,corporation_id,title,year',
                'examBoard:id,name',
            ])
            ->findOrFail($id);
    }

    private function plainText(?string $value): string
    {
        $value = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);
        $value = preg_replace('/\s+/u', ' ', $value) ?: '';

        return trim($value);
    }
}
