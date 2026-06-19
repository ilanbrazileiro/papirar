@extends('layouts.student')

@section('title', 'Simulados')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Simulados</h1>
            <p class="text-muted mb-0">Monte um simulado por corporação, concurso e disciplinas. Os tópicos são definidos automaticamente pelo cadastro do concurso.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Não foi possível gerar o simulado.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <strong>Novo simulado por concurso</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('student.simulated.store') }}" id="simulated-form">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label">Título do simulado</label>
                        <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" placeholder="Ex.: Simulado CHOAE - Matemática e Português">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="quantity" class="form-label">Quantidade de questões</label>
                        <select name="quantity" id="quantity" class="form-control" required>
                            @foreach([10, 20, 30, 40, 50, 80, 100, 120] as $quantity)
                                <option value="{{ $quantity }}" @selected((int) old('quantity', $savedFilter->quantity ?? 20) === $quantity)>{{ $quantity }} questões</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="duration_minutes" class="form-label">Tempo do simulado</label>
                        <div class="input-group">
                            <input type="number" name="duration_minutes" id="duration_minutes" class="form-control" min="5" max="300" value="{{ old('duration_minutes', 60) }}" required>
                            <span class="input-group-text">min</span>
                        </div>
                        <small class="text-muted">Mínimo 5 e máximo 300 minutos.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="corporation_id" class="form-label">Corporação</label>
                        <select name="corporation_id" id="corporation_id" class="form-control" required>
                            <option value="">Selecione...</option>
                            @foreach($corporations as $corporation)
                                <option value="{{ $corporation->id }}" @selected((int) old('corporation_id') === $corporation->id)>{{ $corporation->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="exam_id" class="form-label">Concurso</label>
                        <select name="exam_id" id="exam_id" class="form-control" required disabled>
                            <option value="">Selecione a corporação primeiro</option>
                        </select>
                        <small class="text-muted">Concursos previstos e publicados aparecem aqui.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Disciplinas do concurso</label>
                    <div id="subjects-container" class="border rounded p-3 bg-light">
                        <p class="text-muted mb-0">Selecione um concurso para carregar as disciplinas.</p>
                    </div>
                    <small class="text-muted">No simulado, os tópicos não são escolhidos pelo aluno. O sistema usa os tópicos configurados no admin para o concurso.</small>
                </div>

                <button type="submit" class="btn btn-primary">Gerar simulado</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <strong>Meus simulados</strong>
        </div>
        <div class="card-body">
            @if($simulatedExams->count())
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Concurso</th>
                                <th>Questões</th>
                                <th>Tempo</th>
                                <th>Acertos</th>
                                <th>Aproveitamento</th>
                                <th>Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($simulatedExams as $exam)
                                <tr>
                                    <td>{{ $exam->title }}</td>
                                    <td>{{ optional($exam->exam)->title ?? '-' }}</td>
                                    <td>{{ $exam->total_questions }}</td>
                                    <td>{{ $exam->duration_minutes ? $exam->duration_minutes . ' min' : '-' }}</td>
                                    <td>{{ $exam->correct_answers }}</td>
                                    <td>{{ number_format((float) $exam->accuracy, 2, ',', '.') }}%</td>
                                    <td>
                                        @if($exam->finished_at)
                                            <span class="badge bg-success">Finalizado</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Em andamento</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($exam->finished_at)
                                            <a href="{{ route('student.simulated.result', $exam) }}" class="btn btn-sm btn-outline-primary">Resultado</a>
                                        @else
                                            <a href="{{ route('student.simulated.show', $exam) }}" class="btn btn-sm btn-primary">Continuar</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $simulatedExams->links() }}
            @else
                <p class="text-muted mb-0">Você ainda não gerou nenhum simulado.</p>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const examsByCorporation = @json($examsByCorporation);
    const subjectsByExam = @json($subjectsByExam);
    const oldCorporation = '{{ old('corporation_id') }}';
    const oldExam = '{{ old('exam_id') }}';
    const oldSubjects = @json(array_map('intval', old('subject_ids', [])));

    const corporationSelect = document.getElementById('corporation_id');
    const examSelect = document.getElementById('exam_id');
    const subjectsContainer = document.getElementById('subjects-container');

    function resetExams(message = 'Selecione a corporação primeiro') {
        examSelect.innerHTML = `<option value="">${message}</option>`;
        examSelect.disabled = true;
        subjectsContainer.innerHTML = '<p class="text-muted mb-0">Selecione um concurso para carregar as disciplinas.</p>';
    }

    function loadExams(corporationId, selectedExamId = null) {
        const exams = examsByCorporation[corporationId] || [];

        if (!exams.length) {
            resetExams('Nenhum concurso disponível');
            return;
        }

        examSelect.disabled = false;
        examSelect.innerHTML = '<option value="">Selecione...</option>';

        exams.forEach(function (exam) {
            const option = document.createElement('option');
            option.value = exam.id;
            option.textContent = `${exam.title} ${exam.year ? '(' + exam.year + ')' : ''} - ${exam.status_label}`;
            if (selectedExamId && String(selectedExamId) === String(exam.id)) {
                option.selected = true;
            }
            examSelect.appendChild(option);
        });

        if (selectedExamId) {
            loadSubjects(selectedExamId);
        }
    }

    function loadSubjects(examId) {
        const subjects = subjectsByExam[examId] || [];

        if (!subjects.length) {
            subjectsContainer.innerHTML = '<p class="text-muted mb-0">Nenhuma disciplina vinculada a este concurso.</p>';
            return;
        }

        subjectsContainer.innerHTML = '';

        const actions = document.createElement('div');
        actions.className = 'mb-2';
        actions.innerHTML = `
            <button type="button" class="btn btn-sm btn-outline-primary me-2" id="select-all-subjects">Selecionar todas</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all-subjects">Limpar seleção</button>
        `;
        subjectsContainer.appendChild(actions);

        subjects.forEach(function (subject) {
            const id = `subject_${subject.id}`;
            const checked = oldSubjects.includes(Number(subject.id)) ? 'checked' : '';

            const wrapper = document.createElement('div');
            wrapper.className = 'form-check mb-2';
            wrapper.innerHTML = `
                <input type="checkbox" class="form-check-input subject-checkbox" name="subject_ids[]" value="${subject.id}" id="${id}" ${checked}>
                <label class="form-check-label" for="${id}">
                    <strong>${subject.name}</strong>
                    <span class="text-muted small">(${subject.scope_label})</span>
                </label>
            `;
            subjectsContainer.appendChild(wrapper);
        });

        document.getElementById('select-all-subjects').addEventListener('click', function () {
            document.querySelectorAll('.subject-checkbox').forEach(input => input.checked = true);
        });

        document.getElementById('clear-all-subjects').addEventListener('click', function () {
            document.querySelectorAll('.subject-checkbox').forEach(input => input.checked = false);
        });
    }

    corporationSelect.addEventListener('change', function () {
        loadExams(this.value);
    });

    examSelect.addEventListener('change', function () {
        loadSubjects(this.value);
    });

    if (oldCorporation) {
        loadExams(oldCorporation, oldExam || null);
    }
});
</script>
@endsection
