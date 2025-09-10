<?php
// Dashboard completo (profissional + atalhos de gestão)
// Requisitos: helpers (e(), fetch_all, fetch_value, count_table, base_url)
require_once __DIR__ . '/../config/auth.php'; require_login();
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/partials/header.php';

/* ======================== COLETORES DE DADOS ======================== */

// KPIs
$counts = [
  'products'   => (int)count_table('products'),
  'categories' => (int)count_table('categories'),
  'team'       => (int)count_table('team'),
  'posts'      => (int)count_table('posts'),
  'milestones' => (int)count_table('milestones'),
  'partners'   => (int)count_table('partners'),
  'messages'   => (int)count_table('messages'),
];

// Crescimento de produtos (últimos 7 dias vs 7 dias anteriores)
$last7 = (int)fetch_value("SELECT COUNT(*) FROM products WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)");
$prev7 = (int)fetch_value("SELECT COUNT(*) FROM products WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) AND created_at < DATE_SUB(CURDATE(), INTERVAL 6 DAY)");
$growthProducts = $prev7 ? (($last7 - $prev7) / $prev7) * 100 : ($last7 ? 100 : 0);

// Séries reais
$rowsMsgs = fetch_all("
  SELECT DATE(created_at) d, COUNT(*) v
  FROM messages
  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
  GROUP BY d ORDER BY d ASC
");
$rowsPosts = fetch_all("
  SELECT DATE_FORMAT(COALESCE(published_at, created_at), '%Y-%m') m, COUNT(*) v
  FROM posts
  WHERE COALESCE(published_at, created_at) >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')
  GROUP BY m ORDER BY m ASC
");

// Produtos por status e top categorias
$rowsStatus = fetch_all("SELECT status, COUNT(*) v FROM products GROUP BY status ORDER BY status");
$rowsCats   = fetch_all("
  SELECT c.name label, COUNT(*) v
  FROM products p JOIN categories c ON c.id = p.category_id
  GROUP BY c.id ORDER BY v DESC LIMIT 7
");

// Últimos itens
$latestProducts = fetch_all("SELECT id, title, created_at FROM products ORDER BY created_at DESC LIMIT 5");
$latestMsgs     = fetch_all("SELECT id, name, subject, created_at FROM messages ORDER BY created_at DESC LIMIT 5");

// Uso de mídia
function dir_size(string $path): int {
  $total = 0; if (!is_dir($path)) return 0;
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
  foreach ($it as $f) if ($f->isFile()) $total += $f->getSize();
  return $total;
}
$uploadsMB = round(dir_size(BASE_PATH . '/public/uploads')/(1024*1024), 1);

// Helpers de série
function series_days_fill(array $rows, int $days=14): array {
  $out=[]; $start=new DateTime('-'.($days-1).' days');
  for($i=0;$i<$days;$i++){
    $d=(clone $start)->modify("+{$i} day")->format('Y-m-d');
    $found=0; foreach($rows as $r){ if($r['d']===$d){$found=(int)$r['v']; break;}}
    $out[]=['label'=>DateTime::createFromFormat('Y-m-d',$d)->format('d/m'),'value'=>$found];
  } return $out;
}
function series_months_fill(array $rows, int $months=6): array {
  $out=[]; $start=new DateTime('first day of -'.($months-1).' months');
  for($i=0;$i<$months;$i++){
    $m=(clone $start)->modify("+{$i} month")->format('Y-m');
    $found=0; foreach($rows as $r){ if($r['m']===$m){$found=(int)$r['v']; break;}}
    $out[]=['label'=>DateTime::createFromFormat('Y-m',$m)->format('m/Y'),'value'=>$found];
  } return $out;
}
$seriesMsgs  = series_days_fill($rowsMsgs, 14);
$seriesPosts = series_months_fill($rowsPosts, 6);

// Dados para os charts
$chart = [
  'status' => [
    'labels' => array_map(fn($r)=>$r['status'], $rowsStatus),
    'values' => array_map(fn($r)=>(int)$r['v'], $rowsStatus),
  ],
  'cats'   => [
    'labels' => array_map(fn($r)=>$r['label'], $rowsCats),
    'values' => array_map(fn($r)=>(int)$r['v'], $rowsCats),
  ],
  'msgs'  => $seriesMsgs,
  'posts' => $seriesPosts,
];
?>
<style>
/* ====== visual do dashboard ====== */
.dashboard-wrap{max-width:1120px;margin:12px auto 28px;padding:0 16px}
.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin:10px 0 18px}
.kpi-card{background:#fff;border:1px solid #e7e7e7;border-radius:var(--radius);box-shadow:var(--shadow);padding:14px 16px}
.dark .kpi-card{background:var(--card);border-color:var(--card-border)}
.kpi-top{display:flex;justify-content:space-between;align-items:center;gap:12px}
.kpi-title{margin:0;color:var(--muted);font-weight:700}
.kpi-value{font:800 34px/1 system-ui;margin-top:6px}
.kpi-sub{color:var(--muted);font-size:13px}
.badge{background:rgba(127,166,82,.18);color:var(--brand);padding:4px 8px;border-radius:999px;font-weight:700;font-size:12px}
.delta{font-weight:700}.delta.up{color:#2ecc71}.delta.down{color:#e74c3c}
.btn.inline{padding:8px 12px;border-radius:999px;display:inline-flex;align-items:center;gap:8px}

/* charts */
.charts{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px}
.chart-card{background:#fff;border:1px solid #e7e7e7;border-radius:var(--radius);box-shadow:var(--shadow);padding:12px}
.dark .chart-card{background:var(--card);border-color:var(--card-border)}
.chart-card header{display:flex;align-items:baseline;justify-content:space-between;margin:4px 6px 10px}
.chart-legend{display:flex;gap:10px;flex-wrap:wrap}.legend-item{display:flex;align-items:center;gap:6px;font-size:13px;color:var(--muted)}.legend-dot{width:10px;height:10px;border-radius:999px}
.canvas-tip{position:absolute;pointer-events:none;background:#111;color:#fff;padding:6px 8px;border-radius:8px;font-size:12px;box-shadow:0 8px 24px rgba(0,0,0,.25);transform:translate(-50%,-120%)}
.dark .canvas-tip{background:#000}

/* tabelas & atalhos */
.grid-2{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;margin-top:18px}
.table-card{background:#fff;border:1px solid #e7e7e7;border-radius:var(--radius);box-shadow:var(--shadow);padding:10px 12px}
.dark .table-card{background:var(--card);border-color:var(--card-border)}
.table-card table{width:100%;border-collapse:collapse}
.table-card th,.table-card td{padding:8px;border-top:1px solid #eaeaea}.dark .table-card th,.dark .table-card td{border-color:#223}
.table-card th{text-align:left;color:var(--muted);font-size:13px}
.table-card td a{color:var(--brand-2);text-decoration:underline}

.quick{background:#fff;border:1px solid #e7e7e7;border-radius:var(--radius);box-shadow:var(--shadow);padding:12px}
.dark .quick{background:var(--card);border-color:var(--card-border)}
.quick .actions{display:flex;gap:8px;flex-wrap:wrap}
.quick .actions a{padding:10px 12px;border-radius:12px;background:rgba(127,166,82,.12);color:var(--brand);font-weight:700}
</style>

<div class="dashboard-wrap">
  <h1 class="fade-in">Dashboard</h1>

  <!-- KPIs principais (com botões Gerenciar) -->
  <section class="kpis fade-in">
    <article class="kpi-card">
      <div class="kpi-top">
        <div>
          <p class="kpi-title">Produtos</p>
          <div class="kpi-value"><?= $counts['products'] ?></div>
          <p class="kpi-sub">últimos 7 dias:
            <span class="delta <?= $growthProducts>=0?'up':'down' ?>">
              <?= ($growthProducts>=0?'+':'') . number_format($growthProducts,1) ?>%
            </span>
          </p>
        </div>
        <div>
          <a class="btn inline secondary" href="<?= e(base_url('admin/products/list.php')) ?>">Gerenciar</a>
        </div>
      </div>
      <p class="kpi-sub" style="margin-top:6px">
        <span class="badge"><?= (int)fetch_value("SELECT COUNT(*) FROM products WHERE status='disponivel'") ?> disponíveis</span>
        &nbsp;•&nbsp;
        <span class="badge"><?= (int)fetch_value("SELECT COUNT(*) FROM products WHERE status='em_desenvolvimento'") ?> em dev</span>
      </p>
    </article>

    <article class="kpi-card">
      <div class="kpi-top">
        <div>
          <p class="kpi-title">Categorias</p>
          <div class="kpi-value"><?= $counts['categories'] ?></div>
          <p class="kpi-sub">hierarquia cat/subcat</p>
        </div>
        <a class="btn inline secondary" href="<?= e(base_url('admin/categories/list.php')) ?>">Gerenciar</a>
      </div>
    </article>

    <article class="kpi-card">
      <div class="kpi-top">
        <div>
          <p class="kpi-title">Equipe</p>
          <div class="kpi-value"><?= $counts['team'] ?></div>
          <p class="kpi-sub">fotos + Lattes</p>
        </div>
        <a class="btn inline secondary" href="<?= e(base_url('admin/team/list.php')) ?>">Gerenciar</a>
      </div>
    </article>

    <article class="kpi-card">
      <div class="kpi-top">
        <div>
          <p class="kpi-title">Notícias</p>
          <div class="kpi-value"><?= $counts['posts'] ?></div>
          <p class="kpi-sub">publicações (6 meses no gráfico)</p>
        </div>
        <a class="btn inline secondary" href="<?= e(base_url('admin/posts/list.php')) ?>">Gerenciar</a>
      </div>
    </article>

    <article class="kpi-card">
      <div class="kpi-top">
        <div>
          <p class="kpi-title">Timeline</p>
          <div class="kpi-value"><?= $counts['milestones'] ?></div>
          <p class="kpi-sub">marcos do ToyLab</p>
        </div>
        <a class="btn inline secondary" href="<?= e(base_url('admin/milestones/list.php')) ?>">Gerenciar</a>
      </div>
    </article>

    <article class="kpi-card">
      <div class="kpi-top">
        <div>
          <p class="kpi-title">Parcerias</p>
          <div class="kpi-value"><?= $counts['partners'] ?></div>
          <p class="kpi-sub">logos + links</p>
        </div>
        <a class="btn inline secondary" href="<?= e(base_url('admin/partners/list.php')) ?>">Gerenciar</a>
      </div>
    </article>

    <article class="kpi-card">
      <div class="kpi-top">
        <div>
          <p class="kpi-title">Mensagens</p>
          <div class="kpi-value"><?= $counts['messages'] ?></div>
          <p class="kpi-sub">uploads: <strong><?= $uploadsMB ?> MB</strong></p>
        </div>
        <a class="btn inline secondary" href="<?= e(base_url('admin/messages/list.php')) ?>">Ver</a>
      </div>
    </article>
  </section>

  <!-- GRÁFICOS -->
  <section class="charts fade-in">
    <div class="chart-card">
      <header>
        <h3>Status dos Produtos</h3>
        <div class="chart-legend" id="legend-status"></div>
      </header>
      <div style="position:relative">
        <canvas id="chartStatus" width="420" height="300"
          data-values='<?= e(json_encode($chart['status']['values'])) ?>'
          data-labels='<?= e(json_encode($chart['status']['labels'])) ?>'></canvas>
      </div>
    </div>

    <div class="chart-card">
      <header>
        <h3>Produtos por Categoria (Top 7)</h3>
      </header>
      <div style="position:relative">
        <canvas id="chartCats" width="560" height="300"
          data-values='<?= e(json_encode($chart['cats']['values'])) ?>'
          data-labels='<?= e(json_encode($chart['cats']['labels'])) ?>'></canvas>
      </div>
    </div>

    <div class="chart-card" style="grid-column:1/-1">
      <header>
        <h3>Mensagens (14 dias) &nbsp;•&nbsp; Publicações (6 meses)</h3>
        <span class="kpi-sub">tendência</span>
      </header>
      <div style="position:relative">
        <canvas id="chartLines" width="1040" height="280"
          data-msgs='<?= e(json_encode($chart['msgs'])) ?>'
          data-posts='<?= e(json_encode($chart['posts'])) ?>'></canvas>
      </div>
    </div>
  </section>

  <!-- ÚLTIMOS ITENS + ATALHOS -->
  <section class="grid-2 fade-in">
    <article class="table-card">
      <header><h3>Últimos Produtos</h3></header>
      <table aria-label="Últimos produtos">
        <thead><tr><th>#</th><th>Título</th><th>Criado em</th></tr></thead>
        <tbody>
          <?php foreach($latestProducts as $p): ?>
          <tr>
            <td><?= e((string)$p['id']) ?></td>
            <td><a href="<?= e(base_url('admin/products/edit.php?id='.$p['id'])) ?>">Editar: <?= e($p['title']) ?></a></td>
            <td><?= e(date('d/m/Y H:i', strtotime($p['created_at']))) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </article>

    <article class="table-card">
      <header><h3>Mensagens Recentes</h3></header>
      <table aria-label="Mensagens recentes">
        <thead><tr><th>#</th><th>Remetente</th><th>Assunto</th><th>Data</th></tr></thead>
        <tbody>
          <?php foreach($latestMsgs as $m): ?>
          <tr>
            <td><?= e((string)$m['id']) ?></td>
            <td><?= e($m['name']) ?></td>
            <td><?= e($m['subject']) ?></td>
            <td><?= e(date('d/m/Y H:i', strtotime($m['created_at']))) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </article>
  </section>

  <section class="quick fade-in" style="margin-top:16px;">
    <header><h3>Atalhos Rápidos</h3></header>
    <div class="actions" role="navigation" aria-label="Atalhos do admin">
      <a href="<?= e(base_url('admin/products/create.php')) ?>">+ Novo Produto</a>
      <a href="<?= e(base_url('admin/categories/create.php')) ?>">+ Nova Categoria</a>
      <a href="<?= e(base_url('admin/team/create.php')) ?>">+ Novo Membro</a>
      <a href="<?= e(base_url('admin/posts/create.php')) ?>">+ Nova Notícia</a>
      <a href="<?= e(base_url('admin/milestones/create.php')) ?>">+ Novo Marco</a>
      <a href="<?= e(base_url('admin/partners/create.php')) ?>">+ Novo Parceiro</a>
    </div>
  </section>
</div>

<script>
/* ===== micro chart lib (vanilla, responsiva, sem libs) ===== */
const VAR = k => getComputedStyle(document.documentElement).getPropertyValue(k).trim();
const COLOR_BRAND = VAR('--brand') || '#7fa652';
const COLOR_TEXT  = getComputedStyle(document.body).color;
const COLORS = [COLOR_BRAND, '#06272e', '#6aa6b2', '#d9b04c', '#b06ab3', '#6ac389', '#e67e22'];

function tip(el){ const box=document.createElement('div'); box.className='canvas-tip'; el.parentElement.appendChild(box);
  return { show(x,y,txt){box.textContent=txt;box.style.left=x+'px';box.style.top=y+'px';box.style.display='block';},
           hide(){box.style.display='none';} }; }

function donut(canvas){
  const ctx=canvas.getContext('2d');
  const values=JSON.parse(canvas.dataset.values||'[]');
  const labels=JSON.parse(canvas.dataset.labels||'[]');
  const total=values.reduce((a,b)=>a+b,0)||1;
  const w=canvas.width,h=canvas.height,cx=w/2,cy=h/2,R=Math.min(w,h)*0.38,r=R*0.62;
  ctx.clearRect(0,0,w,h); let a=-Math.PI/2, arcs=[];
  values.forEach((v,i)=>{const a2=a+(v/total)*Math.PI*2; ctx.beginPath(); ctx.arc(cx,cy,R,a,a2); ctx.arc(cx,cy,r,a2,a,true); ctx.closePath();
    ctx.fillStyle=COLORS[i%COLORS.length]; ctx.fill(); arcs.push({a,a2,c:COLORS[i%COLORS.length],label:labels[i],v}); a=a2;});
  ctx.fillStyle=COLOR_TEXT; ctx.font='700 18px system-ui'; ctx.textAlign='center'; ctx.textBaseline='middle'; ctx.fillText(total+' itens',cx,cy);
  const L=document.getElementById('legend-status'); if(L){ L.innerHTML=''; arcs.forEach(seg=>{const it=document.createElement('div'); it.className='legend-item';
    const d=document.createElement('span'); d.className='legend-dot'; d.style.background=seg.c; it.appendChild(d); it.appendChild(document.createTextNode(`${seg.label} (${seg.v})`)); L.appendChild(it);});}
  const t=tip(canvas);
  canvas.onmousemove=e=>{const r=canvas.getBoundingClientRect(),x=e.clientX-r.left,y=e.clientY-r.top,ang=Math.atan2(y-cy,x-cx),angN=ang<-Math.PI/2?ang+Math.PI*2:ang,dist=Math.hypot(x-cx,y-cy);
    if(dist<r||dist>R){t.hide();return;} for(const seg of arcs){if(angN>=seg.a&&angN<=seg.a2){t.show(x,y,`${seg.label}: ${seg.v}`);return;}} t.hide();};
  canvas.onmouseleave=()=>t.hide();
}

function bars(canvas){
  const ctx=canvas.getContext('2d'); const values=JSON.parse(canvas.dataset.values||'[]'); const labels=JSON.parse(canvas.dataset.labels||'[]');
  const w=canvas.width,h=canvas.height,pad=36; ctx.clearRect(0,0,w,h); const max=Math.max(...values,1); const step=(w-pad*2)/values.length;
  ctx.strokeStyle='#ccc3'; ctx.beginPath(); ctx.moveTo(pad,h-28); ctx.lineTo(w-pad,h-28); ctx.stroke();
  values.forEach((v,i)=>{const x=pad+i*step+step*0.15,bw=step*0.7,bh=(v/max)*(h-70); ctx.fillStyle=COLORS[i%COLORS.length]; ctx.shadowColor='#0002'; ctx.shadowBlur=8; ctx.shadowOffsetY=4;
    ctx.fillRect(x,(h-28)-bh,bw,bh); ctx.shadowColor='transparent'; ctx.save(); ctx.translate(x+bw/2,h-8); ctx.rotate(-Math.PI/12); ctx.fillStyle=COLOR_TEXT; ctx.font='12px system-ui'; ctx.textAlign='center';
    ctx.fillText(labels[i]||'',0,0); ctx.restore();});
  const t=tip(canvas);
  canvas.onmousemove=e=>{const r=canvas.getBoundingClientRect(),x=e.clientX-r.left,y=e.clientY-r.top,idx=Math.floor((x-pad)/step);
    if(idx>=0&&idx<values.length){t.show(pad+idx*step+step*0.5,y,`${labels[idx]}: ${values[idx]}`);}else t.hide();};
  canvas.onmouseleave=()=>t.hide();
}

function lines(canvas){
  const ctx=canvas.getContext('2d'); const msgs=JSON.parse(canvas.dataset.msgs||'[]'); const posts=JSON.parse(canvas.dataset.posts||'[]');
  const w=canvas.width,h=canvas.height,pad=40; const all=msgs.concat(posts); const max=Math.max(1,...all.map(x=>x.value)); const gridY=4; ctx.clearRect(0,0,w,h);
  ctx.strokeStyle='#ccc3'; for(let i=0;i<=gridY;i++){const y=pad+i*(h-pad*2)/gridY; ctx.beginPath(); ctx.moveTo(pad,y); ctx.lineTo(w-pad,y); ctx.stroke();}
  function plot(data,color){const step=(w-pad*2)/Math.max(1,(data.length-1)); ctx.beginPath();
    data.forEach((p,i)=>{const x=pad+i*step,y=(h-pad)-(p.value/max)*(h-pad*2); i?ctx.lineTo(x,y):ctx.moveTo(x,y);}); ctx.strokeStyle=color; ctx.lineWidth=2; ctx.stroke();
    const g=ctx.createLinearGradient(0,pad,0,h-pad); g.addColorStop(0,color+'22'); g.addColorStop(1,color+'00'); ctx.lineTo(w-pad,h-pad); ctx.lineTo(pad,h-pad); ctx.closePath(); ctx.fillStyle=g; ctx.fill();}
  plot(msgs,COLOR_BRAND); plot(posts,'#6aa6b2');
  const t=tip(canvas);
  canvas.onmousemove=e=>{const r=canvas.getBoundingClientRect(),x=e.clientX-r.left,y=e.clientY-r.top,step=(w-pad*2)/Math.max(1,(msgs.length-1));
    let idx=Math.round((x-pad)/step); idx=Math.max(0,Math.min(idx,msgs.length-1));
    t.show(pad+idx*step,y,`Msgs ${msgs[idx]?.label||''}: ${msgs[idx]?.value||0} • Posts ${posts[idx]?.label||''}: ${posts[idx]?.value||0}`);};
  canvas.onmouseleave=()=>t.hide();
}

document.addEventListener('DOMContentLoaded', ()=>{
  const c1=document.getElementById('chartStatus'); if(c1) donut(c1);
  const c2=document.getElementById('chartCats');   if(c2) bars(c2);
  const c3=document.getElementById('chartLines');  if(c3) lines(c3);
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
