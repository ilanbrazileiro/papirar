<div class="card" style="border-top:3px solid #f4c542" id="papirar-traffic-panel">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Tráfego do site</h3><span class="float-right small text-muted">Fonte: Google Analytics 4</span></div>
    <div class="card-body">
        <form id="papirar-traffic-filters" class="form-row align-items-end" aria-label="Filtros de tráfego">
            <div class="form-group col-md-3"><label for="traffic-period">Período</label><select class="form-control" id="traffic-period" name="period"><option value="7">Últimos 7 dias completos</option><option value="today">Hoje</option><option value="30">Últimos 30 dias completos</option><option value="month">Mês atual</option><option value="custom">Datas específicas</option></select></div>
            <div class="form-group col-md-2"><label for="traffic-group">Agrupamento</label><select class="form-control" id="traffic-group" name="group"><option value="day">Diário</option><option value="month">Mensal</option></select></div>
            <div class="form-group col-md-2 traffic-custom" hidden><label for="traffic-from">Data inicial</label><input class="form-control" type="date" name="from" id="traffic-from"></div>
            <div class="form-group col-md-2 traffic-custom" hidden><label for="traffic-to">Data final</label><input class="form-control" type="date" name="to" id="traffic-to"></div>
            <div class="form-group col-md-2"><button type="submit" class="btn btn-warning font-weight-bold">Atualizar</button></div>
        </form>
        <div id="traffic-status" class="alert alert-info" role="status">Carregando dados de tráfego...</div>
        <div id="traffic-results" hidden>
            <div id="traffic-range" class="text-muted small mb-3"></div>
            <div class="row text-center" id="traffic-kpis"></div>
            <div class="row"><div class="col-lg-8 mb-3"><div class="border rounded p-3"><h4 class="h6">Visualizações e usuários</h4><div style="position:relative;height:280px"><canvas id="traffic-chart" style="width:100%;height:260px;display:block" aria-label="Evolução de visualizações e usuários" role="img"></canvas><div id="traffic-tip" hidden style="position:absolute;pointer-events:none;background:#102544;color:white;border-radius:8px;padding:8px 11px;font-size:12px;z-index:2;box-shadow:0 8px 20px #10254455"></div></div><p class="small text-muted mt-1 mb-0"><span style="color:#4f81e8">■</span> Visualizações &nbsp; <span style="color:#12376b">■</span> Usuários</p><div id="traffic-tabular" class="sr-only" aria-live="polite"></div></div></div>
            <div class="col-lg-4 mb-3"><div class="border rounded p-3 h-100"><h4 class="h6">Origens do tráfego (sessões)</h4><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Fonte / meio</th><th class="text-right">Sessões</th></tr></thead><tbody id="traffic-sources"></tbody></table></div></div></div></div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const panel=document.getElementById('papirar-traffic-panel');if(!panel)return;
    const form=document.getElementById('papirar-traffic-filters'),period=form.elements.period,group=form.elements.group;
    const status=document.getElementById('traffic-status'),results=document.getElementById('traffic-results');
    const chart=document.getElementById('traffic-chart'),tip=document.getElementById('traffic-tip');
    const format=n=>new Intl.NumberFormat('pt-BR').format(Number(n)||0);
    let series=[],hit=[],controller=null,loaded=false;
    function custom(){form.querySelectorAll('.traffic-custom').forEach(el=>{el.hidden=period.value!=='custom';});}
    period.addEventListener('change',custom);custom();
    function paint(){
        if(!loaded)return;
        const parent=chart.parentElement,w=Math.max(250,parent.clientWidth),h=260,dpr=window.devicePixelRatio||1;
        chart.width=Math.round(w*dpr);chart.height=Math.round(h*dpr);
        const ctx=chart.getContext('2d');ctx.setTransform(dpr,0,0,dpr,0,0);ctx.clearRect(0,0,w,h);
        const left=43,right=12,top=12,bottom=39,gw=w-left-right,gh=h-top-bottom;
        const max=Math.max(1,...series.map(x=>Math.max(x.views,x.users)));
        for(let i=0;i<=4;i++){const y=top+gh*i/4;ctx.strokeStyle='#e6ebf2';ctx.beginPath();ctx.moveTo(left,y);ctx.lineTo(w-right,y);ctx.stroke();ctx.textAlign='right';ctx.font='11px system-ui';ctx.fillStyle='#65758a';ctx.fillText(format(Math.round(max*(4-i)/4)),left-7,y+4);}
        hit=[];
        if(!series.length){ctx.textAlign='center';ctx.fillText('Sem dados no período',w/2,h/2);return;}
        const step=gw/series.length,bw=Math.min(23,step*.32);
        series.forEach((s,i)=>{const mid=left+step*(i+.5),first=mid-bw;
            ctx.fillStyle='#4f81e8';ctx.fillRect(first,top+gh*(1-s.views/max),bw,gh*s.views/max);
            ctx.fillStyle='#12376b';ctx.fillRect(mid,top+gh*(1-s.users/max),bw,gh*s.users/max);
            if(series.length<=16||i%Math.ceil(series.length/12)===0){ctx.fillStyle='#65758a';ctx.textAlign='center';ctx.font='10px system-ui';ctx.fillText(s.label,mid,h-13);}
            hit.push({x:mid-step/2,width:step,label:s.label,views:s.views,users:s.users});
        });
    }
    chart.addEventListener('mousemove',e=>{const rect=chart.getBoundingClientRect();const x=(e.clientX-rect.left)*chart.width/rect.width/(window.devicePixelRatio||1);const point=hit.find(h=>x>=h.x&&x<h.x+h.width);if(!point){tip.hidden=true;return;}tip.textContent=`${point.label} · ${format(point.views)} visualizações · ${format(point.users)} usuários`;tip.hidden=false;tip.style.left=Math.min(rect.width-205,Math.max(0,e.clientX-rect.left+8))+'px';tip.style.top='4px';});
    chart.addEventListener('mouseleave',()=>tip.hidden=true);
    window.addEventListener('resize',paint);
    async function refresh(){
        if(controller)controller.abort();controller=new AbortController();status.hidden=false;status.className='alert alert-info';status.textContent='Consultando GA4...';results.hidden=true;loaded=false;
        const params=new URLSearchParams(new FormData(form));
        try{
            const res=await fetch(@json(route('admin.dashboard.traffic'))+'?'+params.toString(),{credentials:'same-origin',headers:{Accept:'application/json'},signal:controller.signal});const data=await res.json();
            if(!res.ok)throw new Error(Object.values(data.errors||{}).flat().join(' ')||data.message||'Erro ao consultar tráfego.');
            if(!data.available)throw new Error(data.message||'Dados do GA4 indisponíveis.');
            status.hidden=true;results.hidden=false;document.getElementById('traffic-range').textContent=`Período: ${data.from} a ${data.to} · agrupamento ${data.group==='month'?'mensal':'diário'}`;
            const kpis=document.getElementById('traffic-kpis');kpis.replaceChildren();
            for(const [label,value] of [['Visualizações',data.summary.page_views],['Usuários',data.summary.total_users],['Sessões',data.summary.sessions],['Novos usuários',data.summary.new_users]]){const div=document.createElement('div');div.className='col-md-3 col-6 mb-3';const box=document.createElement('div');box.className='border rounded p-3';const name=document.createElement('div');name.className='text-muted small';name.textContent=label;const number=document.createElement('strong');number.className='h3';number.textContent=format(value);box.append(name,number);div.append(box);kpis.append(div);}
            const tbody=document.getElementById('traffic-sources');tbody.replaceChildren();
            for(const item of data.sources||[]){const tr=document.createElement('tr'),a=document.createElement('td'),b=document.createElement('td');a.textContent=(item.dimensions.sessionSource||'(não definido)')+' / '+(item.dimensions.sessionMedium||'(não definido)');b.textContent=format(item.metrics.sessions);b.className='text-right';tr.append(a,b);tbody.append(tr);}
            if(!tbody.childNodes.length){const tr=document.createElement('tr'),td=document.createElement('td');td.colSpan=2;td.textContent='Nenhuma origem registrada';tr.append(td);tbody.append(tr);}
            series=data.series||[];loaded=true;paint();
            document.getElementById('traffic-tabular').textContent=series.map(x=>`${x.label}: ${x.views} visualizações, ${x.users} usuários`).join('; ');
        }catch(e){if(e.name==='AbortError')return;status.hidden=false;status.className='alert alert-warning';status.textContent=e.message||'Erro ao consultar os dados.';results.hidden=true;}
    }
    form.addEventListener('submit',e=>{e.preventDefault();refresh();});
    refresh();
});
</script>
@endpush
