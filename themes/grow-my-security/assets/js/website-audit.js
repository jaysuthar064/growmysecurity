(function () {
	'use strict';
	var app = document.getElementById('gms-audit-app');
	if (!app) return;

	var ajaxUrl = app.dataset.ajaxUrl || '';
	var leadNonce = app.dataset.leadNonce || '';
	var auditNonce = app.dataset.auditNonce || '';
	var defaultStrategy = app.dataset.defaultStrategy || 'desktop';

	var stepHero = document.getElementById('gms-audit-step-hero');
	var stepLoader = document.getElementById('gms-audit-step-loader');
	var stepResults = document.getElementById('gms-audit-step-results');
	var modal = document.getElementById('gms-audit-modal');
	var startBtn = document.getElementById('gms-audit-start-btn');
	var urlError = document.getElementById('gms-audit-url-error');

	var leadForm = document.getElementById('gms-audit-lead-form');
	var leadSubmit = document.getElementById('gms-audit-lead-submit');
	var leadSubmitText = leadSubmit ? leadSubmit.querySelector('.gms-audit-lead-form__submit-text') : null;

	var progressBar = document.getElementById('gms-audit-progress-bar');
	var progressPct = document.getElementById('gms-audit-progress-percent');
	var loaderUrl = document.getElementById('gms-audit-loader-url');
	var loaderNote = document.getElementById('gms-audit-loader-note');
	var scanSteps = Array.prototype.slice.call(document.querySelectorAll('.gms-audit-scan-step'));

	var resultsUrl = document.getElementById('gms-audit-results-url');
	var resultsMeta = document.getElementById('gms-audit-results-meta');
	var issuesList = document.getElementById('gms-audit-issues-list');
	var restartBtn = document.getElementById('gms-audit-restart');
	var downloadBtn = document.getElementById('gms-audit-download-report');
	var strategyButtons = Array.prototype.slice.call(document.querySelectorAll('[data-strategy-button]'));

	var state = { currentUrl:'', currentStrategy:defaultStrategy, resultsCache:{}, inFlight:{}, runId:0, loaderTick:null, loaderTimers:[], lastData:null, logoBase64:null };

	/* ── Preload company logo for PDF ── */
	(function preloadLogo(){
		var logoUrl=app.dataset.ajaxUrl?app.dataset.ajaxUrl.replace(/\/wp-admin\/admin-ajax\.php$/,''):'';
		logoUrl=logoUrl+'/wp-content/themes/grow-my-security/assets/images/logo.png';
		var img=new Image();img.crossOrigin='anonymous';
		img.onload=function(){try{var c=document.createElement('canvas');c.width=img.naturalWidth;c.height=img.naturalHeight;var ctx=c.getContext('2d');ctx.drawImage(img,0,0);state.logoBase64=c.toDataURL('image/png');state.logoNatW=img.naturalWidth;state.logoNatH=img.naturalHeight;}catch(e){console.warn('Logo preload failed:',e);}};
		img.onerror=function(){console.warn('Could not load logo for PDF.');};
		img.src=logoUrl;
	})();

	function normalizeUrl(input) {
		var v = String(input||'').trim();
		if (!v) return '';
		if (!/^https?:\/\//i.test(v)) v = 'https://'+v;
		try { return new URL(v).href; } catch(e) { return ''; }
	}
	function isValidUrl(v) {
		try { var p=new URL(v); return ['http:','https:'].indexOf(p.protocol)!==-1 && p.hostname.indexOf('.')!==-1; } catch(e) { return false; }
	}
	function isValidEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v||'').trim()); }

	function showStep(step) {
		[stepHero,stepLoader,stepResults].forEach(function(n){ if(n){n.classList.remove('is-active');n.hidden=true;} });
		if(step){step.hidden=false;void step.offsetHeight;step.classList.add('is-active');window.scrollTo({top:0,behavior:'smooth'});}
	}
	function openModal() { if(modal) modal.hidden=false; document.body.style.overflow='hidden'; }
	function closeModal() { if(modal) modal.hidden=true; document.body.style.overflow=''; }

	function formatScanTime(v) {
		if(!v) return '';
		var d=new Date(v); if(Number.isNaN(d.getTime())) return '';
		return d.toLocaleString([],{month:'short',day:'numeric',hour:'numeric',minute:'2-digit'});
	}
	function getScoreLevel(v) { if(typeof v!=='number') return 'neutral'; if(v>=80) return 'good'; if(v>=60) return 'average'; return 'poor'; }
	function getScoreColor(v,l) { if(typeof v!=='number') return 'rgba(255,255,255,0.18)'; if(l==='good'||v>=80) return '#22c55e'; if(l==='average'||v>=60) return '#eab308'; return '#ef4444'; }

	function resetLoader() {
		clearLoaderTimers();
		if(progressBar) progressBar.style.width='0%';
		if(progressPct) progressPct.textContent='0%';
		scanSteps.forEach(function(s){s.classList.remove('is-active','is-done');});
		var sh=document.querySelector('.gms-audit-loader__shield'); if(sh) sh.classList.remove('is-animating');
	}
	function clearLoaderTimers() {
		if(state.loaderTick){window.clearInterval(state.loaderTick);state.loaderTick=null;}
		state.loaderTimers.forEach(function(t){window.clearTimeout(t);}); state.loaderTimers=[];
	}
	function setLoaderProgress(v) {
		var c=Math.max(0,Math.min(100,Math.round(v)));
		if(progressBar) progressBar.style.width=c+'%';
		if(progressPct) progressPct.textContent=c+'%';
	}
	function startLoader(strategy) {
		showStep(stepLoader); resetLoader();
		if(loaderUrl) loaderUrl.textContent=state.currentUrl;
		if(loaderNote) loaderNote.textContent='Running live '+(strategy==='mobile'?'Mobile':'Desktop')+' PageSpeed and Mozilla Observatory checks.';
		var sh=document.querySelector('.gms-audit-loader__shield');
		if(sh){void sh.offsetHeight;sh.classList.add('is-animating');}
		var progress=0;
		state.loaderTick=window.setInterval(function(){if(progress>=92) return; progress+=progress<70?2:1; setLoaderProgress(progress);},180);
		[{index:0,delay:0},{index:1,delay:800},{index:2,delay:1800},{index:3,delay:2800},{index:4,delay:3800}].forEach(function(item){
			var tid=window.setTimeout(function(){
				if(item.index>0){scanSteps[item.index-1].classList.remove('is-active');scanSteps[item.index-1].classList.add('is-done');}
				if(scanSteps[item.index]) scanSteps[item.index].classList.add('is-active');
			},item.delay);
			state.loaderTimers.push(tid);
		});
	}
	function finishLoader(cb) {
		clearLoaderTimers();
		scanSteps.forEach(function(s){s.classList.remove('is-active');s.classList.add('is-done');});
		setLoaderProgress(100);
		var tid=window.setTimeout(function(){if(typeof cb==='function') cb();},350);
		state.loaderTimers.push(tid);
	}
	function setStrategyButtons(active,loading) {
		strategyButtons.forEach(function(b){
			var s=b.getAttribute('data-strategy'), isA=s===active, isL=s===loading;
			b.classList.toggle('is-active',isA); b.classList.toggle('is-loading',isL);
			b.disabled=Boolean(loading); b.setAttribute('aria-pressed',isA?'true':'false');
		});
	}
	function buildMetaText(data) {
		var parts=[], meta=data&&data.meta?data.meta:{};
		if(!meta.summary&&data&&data.strategyLabel) parts.push(data.strategyLabel+' strategy');
		if(meta.summary) parts.push(meta.summary);
		if(meta.observatoryGrade) parts.push('Mozilla grade '+meta.observatoryGrade);
		if(meta.scannedAt){var f=formatScanTime(meta.scannedAt);if(f) parts.push('Scanned '+f);}
		if(Array.isArray(meta.messages)&&meta.messages.length) parts.push(meta.messages[0]);
		return parts.filter(Boolean).join(' | ');
	}
	function animateScore(type,scoreData,delay) {
		var circ=326.73, arc=document.querySelector('[data-arc="'+type+'"]');
		var valueEl=document.querySelector('[data-score-value="'+type+'"]');
		var gradeEl=document.querySelector('[data-score-grade="'+type+'"]');
		var value=scoreData&&typeof scoreData.value==='number'?scoreData.value:null;
		var level=scoreData&&scoreData.level?scoreData.level:getScoreLevel(value);
		var label=scoreData&&scoreData.label?scoreData.label:'Unavailable';
		var color=getScoreColor(value,level);
		var offset=value===null?circ:circ-(value/100)*circ;
		window.setTimeout(function(){
			if(arc){arc.style.stroke=color;arc.style.strokeDashoffset=String(offset);}
			if(gradeEl){gradeEl.textContent=label;gradeEl.setAttribute('data-grade-level',level);}
			if(!valueEl) return;
			if(value===null){valueEl.textContent='--';return;}
			var start=performance.now(),dur=900;
			function tick(now){var p=Math.min((now-start)/dur,1),e=1-Math.pow(1-p,3);valueEl.textContent=String(Math.round(e*value));if(p<1) window.requestAnimationFrame(tick);}
			window.requestAnimationFrame(tick);
		},delay||0);
	}
	function createIssueNode(issue) {
		var item=document.createElement('li'); item.className='gms-audit-issue';
		var sev=document.createElement('span'); sev.className='gms-audit-issue__severity gms-audit-issue__severity--'+(issue.severity||'info');
		var content=document.createElement('div'); content.className='gms-audit-issue__content';
		var lbl=document.createElement('p'); lbl.className='gms-audit-issue__label'; lbl.textContent=issue.label||'Untitled issue';
		var desc=document.createElement('p'); desc.className='gms-audit-issue__desc'; desc.textContent=issue.desc||'No additional detail.';
		content.appendChild(lbl); content.appendChild(desc); item.appendChild(sev); item.appendChild(content);
		if(issue.tag){var tag=document.createElement('span');tag.className='gms-audit-issue__tag gms-audit-issue__tag--'+issue.tag;tag.textContent=issue.tag;item.appendChild(tag);}
		return item;
	}
	function renderIssues(issues) {
		if(!issuesList) return; issuesList.innerHTML='';
		if(!Array.isArray(issues)||!issues.length){
			var ei=document.createElement('li');ei.className='gms-audit-issue gms-audit-issue--empty';
			var ec=document.createElement('div');ec.className='gms-audit-issue__content';
			var el=document.createElement('p');el.className='gms-audit-issue__label';el.textContent='No high-priority issues were returned for this scan.';
			ec.appendChild(el);ei.appendChild(ec);issuesList.appendChild(ei);return;
		}
		issues.forEach(function(i){issuesList.appendChild(createIssueNode(i));});
	}
	function renderResults(data) {
		if(!data) return;
		state.lastData=data;
		state.currentStrategy=data.strategy||state.currentStrategy;
		if(resultsUrl) resultsUrl.textContent=data.url||state.currentUrl;
		if(resultsMeta) resultsMeta.textContent=buildMetaText(data);
		setStrategyButtons(state.currentStrategy,null);
		showStep(stepResults);
		var scores=data.scores||{};
		animateScore('security',scores.security||null,120);
		animateScore('performance',scores.performance||null,260);
		animateScore('seo',scores.seo||null,400);
		animateScore('ai_visibility',scores.ai_visibility||null,540);
		renderIssues(data.issues||[]);
	}
	function parseJsonResponse(r) {
		return r.text().then(function(t){if(!t) return null;try{return JSON.parse(t);}catch(e){throw new Error('Unexpected server response.');}});
	}
	function fetchAuditData(strategy) {
		if(state.resultsCache[strategy]) return Promise.resolve(state.resultsCache[strategy]);
		if(state.inFlight[strategy]) return state.inFlight[strategy];
		var rid=state.runId, rurl=state.currentUrl;
		var fd=new FormData();
		fd.append('action','gms_fetch_real_audit_data');fd.append('gms_audit_nonce',auditNonce);
		fd.append('website_url',rurl);fd.append('strategy',strategy);
		var fetchUrl=ajaxUrl+(ajaxUrl.indexOf('?')===-1?'?':'&')+'nocache='+new Date().getTime();
		var rp=fetch(fetchUrl,{method:'POST',body:fd,credentials:'same-origin'})
			.then(parseJsonResponse).then(function(json){
				if(!json||!json.success||!json.data){var m=json&&json.data&&json.data.message?json.data.message:'The live audit could not be completed.';throw new Error(m);}
				if(rid===state.runId&&rurl===state.currentUrl) state.resultsCache[strategy]=json.data;
				return json.data;
			}).finally(function(){if(state.inFlight[strategy]===rp) delete state.inFlight[strategy];});
		state.inFlight[strategy]=rp; return rp;
	}
	function prefetchOtherStrategy() {
		var o=state.currentStrategy==='desktop'?'mobile':'desktop';
		if(!state.currentUrl||state.resultsCache[o]||state.inFlight[o]) return;
		fetchAuditData(o).catch(function(){return null;});
	}
	function handleAuditFailure(error) {
		clearLoaderTimers(); showStep(stepHero);
		if(leadSubmit) leadSubmit.disabled=false;
		if(leadSubmitText) leadSubmitText.textContent='Start My Free Audit';
		if(urlError){urlError.textContent=error&&error.message?error.message:'The live audit could not be completed. Please try again.';urlError.hidden=false;}
	}
	function runInitialAudit() {
		var rid=state.runId, strategy=defaultStrategy;
		state.currentStrategy=strategy; startLoader(strategy);
		fetchAuditData(strategy).then(function(data){
			if(rid!==state.runId) return;
			finishLoader(function(){if(rid!==state.runId) return; renderResults(data); prefetchOtherStrategy();});
		}).catch(function(error){if(rid!==state.runId) return; handleAuditFailure(error);});
	}

	/* ── Reversed flow: form validates URL + lead data, then triggers audit ── */
	function submitLeadAndAudit() {
		var nameEl=document.getElementById('gms-audit-lead-name');
		var emailEl=document.getElementById('gms-audit-lead-email');
		var companyEl=document.getElementById('gms-audit-lead-company');
		var urlEl=document.getElementById('gms-audit-lead-url');
		var valid=true;
		[nameEl,emailEl,urlEl].forEach(function(f){if(!f) return;f.classList.remove('is-invalid');var fe=f.parentElement.querySelector('.gms-audit-field__error');if(fe) fe.textContent='';});
		if(!nameEl||!String(nameEl.value||'').trim()){valid=false;if(nameEl){nameEl.classList.add('is-invalid');var ne=nameEl.parentElement.querySelector('.gms-audit-field__error');if(ne) ne.textContent='Name is required';}}
		if(!emailEl||!isValidEmail(emailEl.value)){valid=false;if(emailEl){emailEl.classList.add('is-invalid');var ee=emailEl.parentElement.querySelector('.gms-audit-field__error');if(ee) ee.textContent='Valid email is required';}}
		var rawUrl=urlEl?String(urlEl.value||'').trim():'';
		var normalized=normalizeUrl(rawUrl);
		if(!normalized||!isValidUrl(normalized)){valid=false;if(urlEl){urlEl.classList.add('is-invalid');var ue=urlEl.parentElement.querySelector('.gms-audit-field__error');if(ue) ue.textContent='Valid website URL is required';}}
		if(!valid) return;

		state.currentUrl=normalized;
		state.runId+=1; state.resultsCache={}; state.inFlight={};
		if(leadSubmit) leadSubmit.disabled=true;
		if(leadSubmitText) leadSubmitText.textContent='Processing...';

		var fd=new FormData();
		fd.append('action','gms_audit_lead');fd.append('gms_audit_nonce',leadNonce);
		fd.append('name',String(nameEl.value||'').trim());
		fd.append('email',String(emailEl.value||'').trim());
		fd.append('company',companyEl?String(companyEl.value||'').trim():'');
		fd.append('website_url',state.currentUrl);

		fetch(ajaxUrl,{method:'POST',body:fd,credentials:'same-origin'}).catch(function(){return null;}).finally(function(){closeModal();runInitialAudit();});
	}

	function handleStrategyToggle(next) {
		if(!next||next===state.currentStrategy||!state.currentUrl) return;
		if(state.resultsCache[next]){renderResults(state.resultsCache[next]);return;}
		setStrategyButtons(state.currentStrategy,next);
		if(resultsMeta) resultsMeta.textContent='Loading live '+(next==='mobile'?'Mobile':'Desktop')+' results...';
		fetchAuditData(next).then(function(d){renderResults(d);}).catch(function(e){
			setStrategyButtons(state.currentStrategy,null);
			if(resultsMeta) resultsMeta.textContent=e&&e.message?e.message:'The requested strategy could not be loaded.';
		});
	}
	function resetAuditState() {
		state.runId+=1;state.currentUrl='';state.currentStrategy=defaultStrategy;state.resultsCache={};state.inFlight={};state.lastData=null;
		clearLoaderTimers();closeModal();
		if(urlError){urlError.textContent='';urlError.hidden=true;}
		if(leadForm) leadForm.reset();
		if(leadSubmit) leadSubmit.disabled=false;
		if(leadSubmitText) leadSubmitText.textContent='Start My Free Audit';
		document.querySelectorAll('.gms-audit-score-card__arc').forEach(function(a){a.style.stroke='rgba(255,255,255,0.18)';a.style.strokeDashoffset='326.73';});
		document.querySelectorAll('[data-score-value]').forEach(function(v){v.textContent='0';});
		document.querySelectorAll('[data-score-grade]').forEach(function(g){g.textContent='';g.setAttribute('data-grade-level','neutral');});
		if(issuesList) issuesList.innerHTML='';
		if(resultsMeta) resultsMeta.textContent='';
		setStrategyButtons(defaultStrategy,null); showStep(stepHero);
	}

	/* ── PDF Report Generator ── */
	function generatePDF() {
		var data=state.lastData;
		if(!data) {
			console.error('Audit data is missing. Cannot generate PDF.');
			alert('No audit data available. Please run a scan first.');
			return;
		}
		
		var jsPDF = null;
		if (typeof window.jspdf !== 'undefined' && window.jspdf.jsPDF) {
			jsPDF = window.jspdf.jsPDF;
		} else if (typeof window.jsPDF !== 'undefined') {
			jsPDF = window.jsPDF;
		}

		if (!jsPDF) {
			console.error('jsPDF library is not loaded.');
			alert('PDF generator library failed to load. Please try refreshing the page.');
			return;
		}

		var doc=new jsPDF({orientation:'portrait',unit:'mm',format:'a4'});
		var w=doc.internal.pageSize.getWidth(), margin=20, cw=w-margin*2;
		var scores=data.scores||{}, issues=data.issues||[], meta=data.meta||{};

		// Header bar
		doc.setFillColor(10,10,15); doc.rect(0,0,w,45,'F');
		doc.setFillColor(239,32,20); doc.rect(0,43,w,2,'F');

		// Logo in upper-right corner
		// Logo in upper-right corner (preserve aspect ratio)
		if(state.logoBase64&&state.logoNatW&&state.logoNatH){try{var lMaxW=45,lMaxH=28,lRatio=state.logoNatW/state.logoNatH,lW=lMaxW,lH=lW/lRatio;if(lH>lMaxH){lH=lMaxH;lW=lH*lRatio;}doc.addImage(state.logoBase64,'PNG',w-margin-lW,8+(28-lH)/2,lW,lH);}catch(e){console.warn('Logo embed failed:',e);}}

		doc.setTextColor(240,240,245); doc.setFontSize(22); doc.setFont('helvetica','bold');
		doc.text('Website Audit Report',margin,22);
		doc.setFontSize(11); doc.setFont('helvetica','normal'); doc.setTextColor(142,142,160);
		doc.text('Grow My Security Company',margin,32);
		doc.text(data.url||state.currentUrl,margin,39);

		var y=55;
		// Scan info
		doc.setFontSize(10); doc.setTextColor(100,100,120);
		doc.text('Strategy: '+(data.strategyLabel||state.currentStrategy),margin,y);
		if(meta.scannedAt) doc.text('Scanned: '+formatScanTime(meta.scannedAt),w-margin,y,{align:'right'});
		y+=12;

		// Scores section
		doc.setFontSize(16); doc.setTextColor(30,30,40); doc.setFont('helvetica','bold');
		doc.text('Audit Scores',margin,y); y+=10;

		var scoreTypes=[
			{key:'security',label:'Security Score'},
			{key:'performance',label:'Performance'},
			{key:'seo',label:'SEO Score'},
			{key:'ai_visibility',label:'AI Visibility'}
		];
		var colW=cw/4;
		scoreTypes.forEach(function(st,i){
			var s=scores[st.key]||{}, val=typeof s.value==='number'?s.value:null;
			var cx=margin+colW*i+colW/2;
			// Score circle background
			var color=val===null?[180,180,180]:val>=80?[34,197,94]:val>=60?[234,179,8]:[239,68,68];
			doc.setFillColor(color[0],color[1],color[2]); doc.circle(cx,y+12,10,'F');
			doc.setTextColor(255,255,255); doc.setFontSize(14); doc.setFont('helvetica','bold');
			doc.text(val!==null?String(val):'--',cx,y+15,{align:'center'});
			doc.setTextColor(60,60,80); doc.setFontSize(10); doc.setFont('helvetica','normal');
			doc.text(st.label,cx,y+28,{align:'center'});
			doc.setFontSize(9); doc.setTextColor(100,100,120);
			doc.text(s.label||'Unavailable',cx,y+34,{align:'center'});
		});
		y+=44;

		// Issues table
		if(issues.length>0){
			doc.setFontSize(16); doc.setTextColor(30,30,40); doc.setFont('helvetica','bold');
			doc.text('Issues Found ('+issues.length+')',margin,y); y+=4;
			var tableData=issues.map(function(iss){
				var sev=(iss.severity||'info').toUpperCase();
				return [sev,iss.label||'',iss.desc||'',(iss.tag||'').toUpperCase()];
			});
			doc.autoTable({
				startY:y, margin:{left:margin,right:margin},
				head:[['Severity','Issue','Description','Category']],
				body:tableData,
				styles:{fontSize:8,cellPadding:3,textColor:[50,50,70],lineColor:[220,220,230],lineWidth:0.3},
				headStyles:{fillColor:[239,32,20],textColor:[255,255,255],fontStyle:'bold',fontSize:9},
				columnStyles:{0:{cellWidth:20,fontStyle:'bold'},3:{cellWidth:22}},
				alternateRowStyles:{fillColor:[248,248,252]},
				didParseCell:function(d){
					if(d.section==='body'&&d.column.index===0){
						var sv=String(d.cell.raw).toLowerCase();
						if(sv==='critical') d.cell.styles.textColor=[239,68,68];
						else if(sv==='warning') d.cell.styles.textColor=[234,179,8];
						else d.cell.styles.textColor=[59,130,246];
					}
				}
			});
			y=doc.lastAutoTable.finalY+12;
		}

		// Recommendations
		if(y>250){doc.addPage();y=20;}
		doc.setFontSize(16); doc.setTextColor(30,30,40); doc.setFont('helvetica','bold');
		doc.text('Recommendations',margin,y); y+=8;
		doc.setFontSize(10); doc.setFont('helvetica','normal'); doc.setTextColor(60,60,80);
		var recs=[];
		if(scores.security&&typeof scores.security.value==='number'&&scores.security.value<80) recs.push('Implement missing security headers (CSP, HSTS, X-Content-Type-Options) to protect against common web attacks.');
		if(scores.performance&&typeof scores.performance.value==='number'&&scores.performance.value<80) recs.push('Optimize page load speed by compressing images, enabling browser caching, and minimizing render-blocking resources.');
		if(scores.seo&&typeof scores.seo.value==='number'&&scores.seo.value<80) recs.push('Improve SEO by adding proper meta descriptions, optimizing heading structure, and ensuring mobile-friendliness.');
		if(scores.ai_visibility&&typeof scores.ai_visibility.value==='number'&&scores.ai_visibility.value<80) recs.push('Boost AI visibility by adding JSON-LD Schema.org markup (Organization, LocalBusiness), allowing AI crawlers (GPTBot, Google-Extended) in robots.txt, and building authoritative brand citations across the web.');
		if(recs.length===0) recs.push('Your website is performing well across all categories. Continue monitoring regularly to maintain these scores.');
		recs.forEach(function(r,i){
			var lines=doc.splitTextToSize((i+1)+'. '+r,cw);
			if(y+lines.length*5>280){doc.addPage();y=20;}
			doc.text(lines,margin,y); y+=lines.length*5+4;
		});

		// Footer CTA
		y+=8; if(y>260){doc.addPage();y=20;}
		doc.setFillColor(248,248,252); doc.roundedRect(margin,y,cw,28,3,3,'F');
		doc.setFontSize(12); doc.setFont('helvetica','bold'); doc.setTextColor(30,30,40);
		doc.text('Need Expert Help?',margin+8,y+10);
		doc.setFontSize(9); doc.setFont('helvetica','normal'); doc.setTextColor(100,100,120);
		doc.text('Contact Grow My Security Company: info@growmysecuritycompany.com',margin+8,y+18);
		doc.text('Visit us at growmysecuritycompany.com',margin+8,y+24);

		// Watermark footer on every page
		var pages=doc.internal.getNumberOfPages();
		for(var p=1;p<=pages;p++){
			doc.setPage(p);
			doc.setFontSize(8); doc.setTextColor(180,180,190);
			doc.text('Generated by Grow My Security Company | '+new Date().toLocaleDateString(),margin,290);
			doc.text('Page '+p+' of '+pages,w-margin,290,{align:'right'});
		}

		var hostname='';
		try{hostname=new URL(data.url||state.currentUrl).hostname.replace(/[^a-zA-Z0-9.-]/g,'');}catch(e){hostname='website';}
		doc.save('audit-report-'+hostname+'.pdf');
	}

	/* ── Event Listeners ── */
	// Hero CTA button opens the form modal directly
	if(startBtn){
		startBtn.addEventListener('click',function(){
			if(urlError){urlError.textContent='';urlError.hidden=true;}
			openModal();
		});
	}

	document.querySelectorAll('[data-audit-modal-close]').forEach(function(el){el.addEventListener('click',closeModal);});
	document.addEventListener('keydown',function(e){if(e.key==='Escape'&&modal&&!modal.hidden) closeModal();});

	if(leadForm){leadForm.addEventListener('submit',function(e){e.preventDefault();submitLeadAndAudit();});}
	strategyButtons.forEach(function(b){b.addEventListener('click',function(){handleStrategyToggle(b.getAttribute('data-strategy'));});});
	if(restartBtn){restartBtn.addEventListener('click',function(){resetAuditState();});}
	if(downloadBtn){downloadBtn.addEventListener('click',function(){generatePDF();});}
})();
