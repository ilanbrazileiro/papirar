<div class="card" style="border-top:3px solid #f4c542"><div class="card-header"><h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Tráfego do site · últimos 7 dias completos</h3><div class="card-tools text-muted small">Fonte: Google Analytics 4</div></div>
<div class="card-body">
@if(!($traffic['available']??false))<div class="alert alert-info mb-0">{{ $traffic['message']??'Dados de tráfego indisponíveis.' }}</div>
@else
<p class="text-muted small">Período: {{ $traffic['from'] }} a {{ $traffic['to'] }} · visualizações e usuários não são a mesma métrica.</p>
<div class="row text-center">
<div class="col-md-3 col-6 mb-3"><div class="border rounded p-3"><div class="text-muted small">Visualizações</div><strong class="h3">{{ number_format($traffic['summary']['page_views']??0,0,',','.') }}</strong></div></div>
<div class="col-md-3 col-6 mb-3"><div class="border rounded p-3"><div class="text-muted small">Usuários</div><strong class="h3">{{ number_format($traffic['summary']['total_users']??0,0,',','.') }}</strong></div></div>
<div class="col-md-3 col-6 mb-3"><div class="border rounded p-3"><div class="text-muted small">Sessões</div><strong class="h3">{{ number_format($traffic['summary']['sessions']??0,0,',','.') }}</strong></div></div>
<div class="col-md-3 col-6 mb-3"><div class="border rounded p-3"><div class="text-muted small">Novos usuários</div><strong class="h3">{{ number_format($traffic['summary']['new_users']??0,0,',','.') }}</strong></div></div>
</div>
<div class="row"><div class="col-lg-8 mb-3"><div class="border rounded p-3" style="min-height:300px"><h4 class="h6">Evolução diária</h4><div style="position:relative;height:255px"><canvas id="papirar-traffic-chart" role="img" aria-label="Gráfico de visualizações e usuários por dia"></canvas></div><p class="small text-muted mt-2 mb-0"><span style="color:#4f81e8">■</span> Visualizações <span style="color:#12376b">■</span> Usuários</p></div></div>
<div class="col-lg-4 mb-3"><div class="border rounded p-3 h-100"><h4 class="h6">Origem do tráfego (sessões)</h4><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Fonte / meio</th><th class="text-right">Sessões</th></tr></thead><tbody>
@forelse($traffic['sources'] as $item)<tr><td>{{ ($item['dimensions']['sessionSource']??'(não definido)').' / '.($item['dimensions']['sessionMedium']??'(não definido)') }}</td><td class="text-right">{{ number_format((int)($item['metrics']['sessions']??0),0,',','.') }}</td></tr>
@empty<tr><td colspan="2">Nenhuma origem registrada no período.</td></tr>
@endforelse</tbody></table></div></div></div></div>
@endif
</div></div>
@if($traffic['available']??false)
@push('scripts')<script>
document.addEventListener('DOMContentLoaded',function(){
 const canvas=document.getElementById('papirar-traffic-chart');if(!canvas)return;
 const days=@json($traffic['dates']);const views=@json($traffic['views']);const users=@json($traffic['users']);const ctx=canvas.getContext('2d');
 function render(){const dpr=window.devicePixelRatio||1;const w=canvas.parentElement.clientWidth,h=250;canvas.width=Math.round(w*dpr);canvas.height=Math.round(h*dpr);ctx.setTransform(dpr,0,0,dpr,0,0);ctx.clearRect(0,0,w,h);
 const left=42,right=15,top=12,bottom=34,gw=w-left-right,gh=h-top-bottom;const max=Math.max(1,...views,...users);const step=gw/7;ctx.font='12px system-ui';ctx.fillStyle='#627089';ctx.textAlign='right';
 for(let i=0;i<=4;i++){let y=top+gh*i/4;ctx.strokeStyle='#e8edf4';ctx.beginPath();ctx.moveTo(left,y);ctx.lineTo(w-right,y);ctx.stroke();ctx.fillText(String(Math.round(max*(4-i)/4)),left-9,y+4);}
 days.forEach((day,i)=>{let x=left+step*i+step*.15,bw=step*.29;ctx.fillStyle='#4f81e8';ctx.fillRect(x,top+gh*(1-views[i]/max),bw,gh*views[i]/max);ctx.fillStyle='#12376b';ctx.fillRect(x+bw,top+gh*(1-users[i]/max),bw,gh*users[i]/max);ctx.fillStyle='#627089';ctx.textAlign='center';ctx.fillText(day.slice(8,10)+'/'+day.slice(5,7),left+step*i+step*.48,h-10);});}
 render();window.addEventListener('resize',render);
});
</script>
@endpush
@endif
