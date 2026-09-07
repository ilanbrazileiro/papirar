@once @push('styles')<style>
.revenue-alert{padding:22px;border:1px solid #f2d486;border-radius:21px;background:linear-gradient(135deg,#fff8df,#fff);box-shadow:0 12px 32px rgba(15,35,68,.07)}.revenue-grid{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:20px;align-items:center}.revenue-kicker{color:#9a6b00;font-size:.75rem;font-weight:900;text-transform:uppercase;letter-spacing:.07em}.revenue-actions{display:grid;gap:8px;min-width:230px}@media(max-width:767.98px){.revenue-grid{grid-template-columns:1fr}.revenue-actions{min-width:0}.revenue-actions .btn{width:100%}}
</style>@endpush @endonce
@php $course=$revenueNotice['course'];$type=$revenueNotice['type'];$transaction=$revenueNotice['transaction'];$access=$revenueNotice['access']; @endphp
<section class="revenue-alert mb-4" data-revenue-notice data-notice-type="{{ $type }}" data-course-id="{{ $course->id }}">
 <div class="revenue-grid"><div><div class="revenue-kicker">@if($type==='pending') Pagamento pendente @elseif($type==='failed') Pagamento não concluído @else Acesso próximo do vencimento @endif</div>
 <h2 class="h4 fw-bold mb-1">{{ $course->title }}</h2>
 <div class="small-muted">@if($type==='pending') Sua compra aguarda conclusão ou confirmação. @elseif($type==='failed') Você pode gerar uma nova tentativa com segurança. @else Seu acesso termina em {{ $access->ends_at->format('d/m/Y') }}. O novo período será somado ao atual. @endif</div></div>
 <div class="revenue-actions">
  @if($type==='pending' && $transaction->checkoutUrl())<a class="btn btn-warning" href="{{ $transaction->checkoutUrl() }}" data-revenue-action="resume">CONTINUAR PAGAMENTO →</a>
  @else
   @foreach($course->availableBillingCycles() as $cycle=>$label)<form method="POST" action="{{ route('student.courses.checkout',$course) }}" data-revenue-action="{{ $type==='expiring' ? 'renew' : 'retry' }}">@csrf<input type="hidden" name="billing_cycle" value="{{ $cycle }}"><button class="btn {{ $loop->first ? 'btn-warning' : 'btn-outline-primary' }} w-100">{{ $type==='expiring' ? 'Renovar' : 'Tentar novamente' }} {{ $label }}</button></form>@endforeach
  @endif
  @if($type==='expiring')<button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#non-renew-{{ $access->id }}">Não pretendo renovar</button>@endif
 </div></div>
 @if($type==='expiring')<div class="collapse mt-3" id="non-renew-{{ $access->id }}"><form method="POST" action="{{ route('student.retention-feedback.store',$access) }}" class="row g-2" data-retention-feedback>@csrf<div class="col-md-5"><select class="form-select" name="reason" required><option value="">Selecione o motivo</option><option value="price">Preço</option><option value="not_using">Não estou utilizando</option><option value="exam_finished">Minha prova já aconteceu</option><option value="content">Conteúdo não atende</option><option value="usability">Dificuldade para usar</option><option value="other">Outro</option></select></div><div class="col-md-5"><input class="form-control" name="notes" maxlength="500" placeholder="Comentário opcional"></div><div class="col-md-2"><button class="btn btn-outline-secondary w-100">Enviar</button></div><div class="small-muted">Isso não encerra seu acesso atual e não gera cobrança automática.</div></form></div>@endif
</section>
