@extends('layouts.app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <div class="container py-4" id="bms-config"
         data-endpoint-preview="{{ route('bms.preview') }}">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="mb-0">🔧 Configure BMS Parameters</h3>
                    <span class="text-muted small">Batch configure — per vendor, grouped by identical params</span>
                </div>

                {{-- STATUS / HELPER --}}
                <div class="alert alert-info d-flex align-items-start gap-3" role="alert">
                    <div>ℹ️</div>
                    <div class="small mb-0">
                        Select at least one battery, optionally preview online status and current settings,
                        then apply a parameter template to all or customize per battery.<br>
                        <strong>Units:</strong> cell thresholds in <code>mV</code>, pack thresholds in <code>V</code>,
                        currents in <code>A</code>, durations / recovery delays in <code>s</code>, temps in <code>°C</code>,
                        capacity in <code>Ah or mAh</code>. Balanced start is in <code>mV</code>. Low battery alarm is in <code>%</code>.
                    </div>
                </div>

                <form method="POST" action="{{ route('bms.send') }}" id="bms-form" novalidate>
                    @csrf

                    {{-- BATTERY PICKER --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="mb-2">
                                <label for="battery-select" class="form-label">📦 Select Batteries <span class="text-muted">(min 1)</span></label>
                                <div class="d-flex gap-2">
                                    <select id="battery-select" class="form-select" aria-label="Select a battery">
                                        <option value="">-- Select a battery --</option>
                                        @foreach($batteries as $battery)
                                            <option value="{{ $battery->mac_id }}">{{ $battery->mac_id }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" id="btn-add-battery" class="btn btn-outline-primary">Add</button>
                                </div>
                                <div class="form-text">Tip: choosing a value also auto-adds it.</div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label mb-1">✅ Selected Batteries</label>
                                <div id="selected-batteries" class="selected-badges border rounded p-2" aria-live="polite">
                                    <em class="text-muted">No batteries selected</em>
                                </div>
                                <div class="d-flex gap-2 mt-2">
                                    <button type="button" id="clear-batteries" class="btn btn-sm btn-outline-danger d-none">🗑️ Clear All</button>
                                    <button type="button" id="preview-btn" class="btn btn-sm btn-outline-secondary d-none">🔍 Preview status & settings</button>
                                </div>
                            </div>

                            <input type="hidden" name="batteries" id="batteries-hidden" />
                        </div>
                    </div>

                    {{-- CONFIG MODE --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <label class="form-label">🎛️ Configuration Mode</label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="configMode" id="mode-all" value="all" checked>
                                    <label class="form-check-label" for="mode-all">Apply base parameters to all selected</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="configMode" id="mode-per" value="per">
                                    <label class="form-check-label" for="mode-per">Customize per battery (overrides)</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- BASE PARAMETERS --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">⚙️ Base BMS Parameters</h5>
                                <button type="button" id="btn-validate" class="btn btn-sm btn-outline-primary">
                                    ✅ Validate rules
                                </button>
                            </div>
                            <p class="text-muted small mt-1 mb-3">
                                Validation checks ordering rules from the API (cell/pack ladders, current gears, temp ladders).
                            </p>

                            @php
                                $params = [
                                  // --- Cell thresholds (mV)
                                  'CPBV'   => 'Cell charge protect voltage (mV)',
                                  'SRBV'   => 'Cell charging recovery voltage (mV)',
                                  'DRBV'   => 'Cell discharge recovery voltage (mV)',
                                  'DPBV'   => 'Cell discharge protect voltage (mV)',

                                  // --- Pack thresholds (V)
                                  'ASCPV'  => 'Total charge protect voltage (V)',
                                  'ACPBV'  => 'Total charge recovery voltage (V)',
                                  'ASRBV'  => 'Charge resume voltage (vendor-specific) (V)',
                                  'ADRBV'  => 'Total discharge recovery voltage (V)',
                                  'ADPBV'  => 'Total discharge protect voltage (V)',
                                  'ASDPV'  => 'Secondary discharge protect voltage (V)',

                                  // --- Others
                                  'SSPV'   => 'Cell dropout protect voltage (mV)',
                                  'ESBV'   => 'Balanced start control voltage (mV)',
                                  'LRLAV'  => 'Low battery alarm (%)',

                                  // Capacity & equalizer
                                  'BCRL'   => 'Nominal Capacity (Ah or mAh)',
                                  'EMST'   => 'Equalizer Switch (0/1)',

                                  // Charging overcurrent gears (A / s)
                                  'NCHPCA_1' => 'Charging overcurrent gear1: current (A)',
                                  'NCHPDT_1' => 'Charging overcurrent gear1: duration (s)',
                                  'NCHPRT_1' => 'Charging overcurrent gear1: recovery delay (s)',

                                  'NCHPCA_2' => 'Charging overcurrent gear2: current (A)',
                                  'NCHPDT_2' => 'Charging overcurrent gear2: duration (s)',
                                  'NCHPRT_2' => 'Charging overcurrent gear2: recovery delay (s)',

                                  'NCHPCA_3' => 'Charging overcurrent gear3: current (A)',
                                  'NCHPDT_3' => 'Charging overcurrent gear3: duration (s)',
                                  'NCHPRT_3' => 'Charging overcurrent gear3: recovery delay (s)',

                                  // Discharge overcurrent gears (A / s)
                                  'NDHPCA_1' => 'Discharge overcurrent gear1: current (A)',
                                  'NDHPDT_1' => 'Discharge overcurrent gear1: duration (s)',
                                  'NDHPRT_1' => 'Discharge overcurrent gear1: recovery delay (s)',

                                  'NDHPCA_2' => 'Discharge overcurrent gear2: current (A)',
                                  'NDHPDT_2' => 'Discharge overcurrent gear2: duration (s)',
                                  'NDHPRT_2' => 'Discharge overcurrent gear2: recovery delay (s)',

                                  'NDHPCA_3' => 'Discharge overcurrent gear3: current (A)',
                                  'NDHPDT_3' => 'Discharge overcurrent gear3: duration (s)',
                                  'NDHPRT_3' => 'Discharge overcurrent gear3: recovery delay (s)',

                                  // Temps (°C)
                                  'NPCMOP_1' => 'Temp1 over-protect (°C)',  'NPCMOR_1' => 'Temp1 over-recovery (°C)', 'NPCMUP_1' => 'Temp1 under-protect (°C)', 'NPCMUR_1' => 'Temp1 under-recovery (°C)',
                                  'NPCMOP_2' => 'Temp2 over-protect (°C)',  'NPCMOR_2' => 'Temp2 over-recovery (°C)', 'NPCMUP_2' => 'Temp2 under-protect (°C)', 'NPCMUR_2' => 'Temp2 under-recovery (°C)',
                                  'NPCMOP_3' => 'Temp3 over-protect (°C)',  'NPCMOR_3' => 'Temp3 over-recovery (°C)', 'NPCMUP_3' => 'Temp3 under-protect (°C)', 'NPCMUR_3' => 'Temp3 under-recovery (°C)',
                                  'NPCMOP_4' => 'Temp4 over-protect (°C)',  'NPCMOR_4' => 'Temp4 over-recovery (°C)', 'NPCMUP_4' => 'Temp4 under-protect (°C)', 'NPCMUR_4' => 'Temp4 under-recovery (°C)',
                                  'NPCMOP_5' => 'Temp5 over-protect (°C)',  'NPCMOR_5' => 'Temp5 over-recovery (°C)', 'NPCMUP_5' => 'Temp5 under-protect (°C)', 'NPCMUR_5' => 'Temp5 under-recovery (°C)',
                                ];
                            @endphp

                            <div class="row g-3" id="base-params">
                                @foreach($params as $param => $label)
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="param-{{ $param }}">
                                            {{ $label }}
                                        </label>
                                        <input
                                            id="param-{{ $param }}"
                                            name="params[{{ $param }}]"
                                            class="form-control param-input"
                                            inputmode="decimal"
                                            autocomplete="off"
                                            placeholder="{{ $label }}"
                                        >
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- PER-BATTERY OVERRIDES --}}
                    <div class="card mb-3 d-none" id="overrides-card" aria-hidden="true">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">🧩 Per-battery Overrides</h5>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="copy-base-to-all">Copy base values to all</button>
                            </div>
                            <p class="text-muted small mt-1 mb-3">Only fill fields you want to override; others inherit from Base.</p>

                            <div id="overrides-container" class="accordion"></div>
                        </div>
                    </div>

                    {{-- COMMAND (vendor spec: SendCommands requires cmd[/param]) --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title mb-2">🛰️ Command</h5>
                            <p class="text-muted small mt-0 mb-3">
                                Provide the vendor command key (<code>cmd</code>) and, if required, its parameter (<code>param</code>).
                                Example: <code>MTS_BMS_SETTING</code> with a hex blob in <code>param</code>.
                            </p>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="cmd-input">Command (cmd) <span class="text-danger">*</span></label>
                                    <input id="cmd-input" name="cmd" class="form-control" placeholder="e.g. MTS_BMS_SETTING" required>
                                    <div class="form-text">Required by SendCommands.</div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="cmd-presets">Presets</label>
                                    <select id="cmd-presets" class="form-select" aria-label="Command presets">
                                        <option value="">-- choose preset --</option>
                                        <option value="MTS_BMS_SETTING">MTS_BMS_SETTING (push settings)</option>
                                        {{-- add more presets if needed --}}
                                    </select>
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="auto-param" checked>
                                        <label class="form-check-label" for="auto-param">
                                            Auto-build param on server (recommended)
                                        </label>
                                    </div>
                                    <input type="hidden" name="auto_param" id="auto-param-hidden" value="1">

                                    <label class="form-label" for="param-input">Parameter (param)</label>
                                    <textarea id="param-input" name="param" class="form-control" rows="3"
                                              placeholder="Hex blob or vendor-defined string (manual mode only)"
                                              disabled></textarea>
                                    <div class="form-text">
                                        When auto-build is on, the server will read the device’s <code>Seting</code>, overlay your changes,
                                        and generate the vendor hex. Turn this off to paste a manual hex <code>param</code>.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PREVIEW RESULTS --}}
                    <div class="card mb-3 d-none" id="preview-card" aria-live="polite" aria-hidden="true">
                        <div class="card-body">
                            <h5 class="card-title">🔍 Preview (status & selected settings)</h5>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th>Battery</th>
                                        <th>Status</th>
                                        <th>Last Seen</th>
                                        <th>CHG</th>
                                        <th>DIS</th>
                                        <th>Pack V</th>
                                        <th>SOC</th>
                                    </tr>
                                    </thead>
                                    <tbody id="preview-tbody"></tbody>
                                </table>
                            </div>
                            <div class="small text-muted mt-2">Note: fields shown depend on vendor response; full settings are read per device.</div>
                        </div>
                    </div>

                    {{-- SUBMIT --}}
                    <div class="d-grid d-md-flex gap-2 justify-content-md-end">
                        <button type="submit" class="btn btn-primary" id="submit-button" disabled>
                            <span id="submit-text">✅ Send BMS Commands</span>
                            <span id="submit-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Toasts --}}
    <div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1090;"></div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .selected-badges { min-height: 48px; background: var(--bs-light-bg-subtle, #f8f9fa); }
        .chip {
            --p: .5rem;
            display: inline-flex; align-items: center; gap: .5rem;
            padding: var(--p) .75rem; margin: .25rem .25rem 0 0;
            background: var(--bs-success-bg-subtle, #d1e7dd);
            color: var(--bs-emphasis-color, #212529);
            border: 1px solid var(--bs-success-border-subtle, #a3cfbb);
            border-radius: 999px;
            font-size: .9rem;
        }
        .chip .chip-remove { cursor: pointer; line-height: 1; }
        .is-invalid { border-color: var(--bs-danger, #dc3545) !important; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => { 'use strict';

            const root = document.querySelector('#bms-config');
            if (!root) return;

            const $  = (sel, ctx=document) => ctx.querySelector(sel);
            const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));

            const endpointPreview = root.dataset.endpointPreview || '';
            const csrfToken = $('meta[name="csrf-token"]').getAttribute('content');

            // Elements
            const selectEl      = $('#battery-select');
            const badgesWrap    = $('#selected-batteries');
            const clearBtn      = $('#clear-batteries');
            const previewBtn    = $('#preview-btn');

            const form          = $('#bms-form');
            const submitBtn     = $('#submit-button');
            const submitText    = $('#submit-text');
            const submitSpinner = $('#submit-spinner');

            const modeAll       = $('#mode-all');
            const modePer       = $('#mode-per');
            const overridesCard = $('#overrides-card');
            const overridesAcc  = $('#overrides-container');
            const copyBaseBtn   = $('#copy-base-to-all');

            const baseParamsWrap= $('#base-params');
            const validateBtn   = $('#btn-validate');

            const previewCard   = $('#preview-card');
            const previewTbody  = $('#preview-tbody');

            const hiddenInput   = $('#batteries-hidden');
            const toastContainer= $('#toast-container');

            // Command inputs
            const cmdInput      = $('#cmd-input');
            const cmdPresets    = $('#cmd-presets');
            const paramInput    = $('#param-input');
            const autoParam     = $('#auto-param');
            const autoParamHidden = $('#auto-param-hidden');

            let selected = [];

            // Tooltips
            $$('.param-input [data-bs-toggle="tooltip"], [data-bs-toggle="tooltip"]').forEach(el=> new bootstrap.Tooltip(el));

            function makeToast(message, variant='info'){
                const id = `t-${Date.now()}`;
                const html = `
<div id="${id}" class="toast align-items-center text-bg-${variant} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
  <div class="d-flex">
    <div class="toast-body">${message}</div>
    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
  </div>
</div>`;
                toastContainer.insertAdjacentHTML('beforeend', html);
                const el = $('#'+id);
                (new bootstrap.Toast(el)).show();
                el.addEventListener('hidden.bs.toast', ()=> el.remove());
            }

            function updateHidden(){ hiddenInput.value = selected.join(','); }

            function canSubmit() {
                return selected.length >= 1 && (cmdInput.value || '').trim() !== '';
            }

            function updateSubmitState(){
                submitBtn.disabled = !canSubmit();
                previewBtn.classList.toggle('d-none', selected.length === 0);
                clearBtn.classList.toggle('d-none', selected.length === 0);
            }

            function renderBadges(){
                badgesWrap.innerHTML = '';
                if (selected.length === 0){
                    badgesWrap.innerHTML = '<em class="text-muted">No batteries selected</em>';
                } else {
                    selected.forEach(mac=>{
                        const chip = document.createElement('span');
                        chip.className = 'chip';
                        chip.innerHTML = `
<span class="fw-semibold">${mac}</span>
<button type="button" class="btn btn-sm btn-link link-danger p-0 chip-remove" aria-label="Remove ${mac}">
  <i class="bi bi-x-circle"></i>
</button>`;
                        chip.querySelector('.chip-remove').addEventListener('click', ()=>{
                            selected = selected.filter(x=>x!==mac);
                            updateHidden(); renderBadges(); renderOverrides(); updateSubmitState();
                        });
                        badgesWrap.appendChild(chip);
                    });
                }
                updateSubmitState();
            }

            // Add handlers
            function addMacFromSelect(){
                const mac = (selectEl.value || '').trim();
                if (!mac) { makeToast('Please select a battery first.', 'warning'); return; }
                if (selected.includes(mac)) { makeToast(`Battery ${mac} is already selected.`, 'info'); return; }
                selected.push(mac);
                updateHidden(); renderBadges(); renderOverrides(); updateSubmitState();
                selectEl.value = ''; selectEl.focus();
            }
            document.addEventListener('click', (e)=>{
                const btn = e.target.closest('#btn-add-battery');
                if (!btn) return;
                e.preventDefault();
                addMacFromSelect();
            });
            selectEl.addEventListener('change', addMacFromSelect);
            selectEl.addEventListener('keydown', (e)=>{ if (e.key === 'Enter') { e.preventDefault(); addMacFromSelect(); } });

            // Clear
            clearBtn.addEventListener('click', ()=>{
                selected = [];
                updateHidden(); renderBadges(); renderOverrides();
                previewCard.classList.add('d-none'); previewCard.setAttribute('aria-hidden', 'true');
                previewTbody.innerHTML = '';
                updateSubmitState();
            });

            // Mode toggle
            const toggleOverrides = ()=>{
                const on = modePer.checked;
                overridesCard.classList.toggle('d-none', !on);
                overridesCard.setAttribute('aria-hidden', String(!on));
            };
            modeAll.addEventListener('change', toggleOverrides);
            modePer.addEventListener('change', toggleOverrides);

            // Overrides
            function renderOverrides(){
                if (!modePer.checked){ return; }
                overridesAcc.innerHTML = '';
                selected.forEach((mac, idx)=>{
                    const itemId = `ov-${idx}`;
                    const panel = document.createElement('div');
                    panel.className = 'accordion-item';
                    panel.innerHTML = `
<h2 class="accordion-header" id="h-${itemId}">
  <button class="accordion-button ${idx>0?'collapsed':''}" type="button"
          data-bs-toggle="collapse" data-bs-target="#c-${itemId}"
          aria-expanded="${idx===0?'true':'false'}" aria-controls="c-${itemId}">
    ${mac}
  </button>
</h2>
<div id="c-${itemId}" class="accordion-collapse collapse ${idx===0?'show':''}"
     aria-labelledby="h-${itemId}" data-bs-parent="#${overridesAcc.id}">
  <div class="accordion-body">
    <div class="row g-3">
      ${$$('.param-input', baseParamsWrap).map(inp=>{
                        const key = inp.name.match(/\[(.+)\]/)?.[1] ?? '';
                        const lid = `ov-${mac}-${key}`;
                        const label = inp.closest('.col-12')?.querySelector('label')?.innerText?.trim() || key;
                        return `
        <div class="col-12 col-md-6">
          <label class="form-label" for="${lid}">${label}</label>
          <input id="${lid}" class="form-control ov-input"
                 name="overrides[${mac}][${key}]"
                 placeholder="(inherit)" inputmode="decimal" autocomplete="off">
        </div>`;
                    }).join('')}
    </div>
  </div>
</div>`;
                    overridesAcc.appendChild(panel);
                });
            }

            // Copy base to overrides
            $('#copy-base-to-all').addEventListener('click', ()=>{
                const baseValues = {};
                $$('.param-input', baseParamsWrap).forEach(inp=>{
                    const key = inp.name.match(/\[(.+)\]/)?.[1];
                    if (key) baseValues[key] = inp.value;
                });
                $$('.ov-input', overridesAcc).forEach(ov=>{
                    const key = ov.name.match(/\[([^\]]+)\]$/)?.[1];
                    if (key && baseValues[key] !== undefined) ov.value = baseValues[key];
                });
                makeToast('Copied base values into all override fields.', 'success');
            });

            // Validation helpers
            function parseNum(v){ if(v===undefined||v===null||v==='') return null; const n=Number(v); return Number.isNaN(n)?null:n; }
            function vmap(scopeSel){
                const out = {};
                $$('.param-input', scopeSel || document).forEach(inp=>{
                    const key = inp.name.match(/\[(.+)\]/)?.[1];
                    if (key) out[key] = parseNum(inp.value);
                });
                return out;
            }
            function validateParams(p){
                const fail = (msg, field)=>({ok:false, msg, field});
                const gt = (a,b) => (a==null||b==null) ? true : a>b;

                const pairs = [
                    ['CPBV','SRBV','>'], ['SRBV','DRBV','>'], ['DRBV','DPBV','>'],
                    ['ASCPV','ACPBV','>'], ['ACPBV','ASRBV','>'], ['ASRBV','ADRBV','>'],
                    ['ADRBV','ADPBV','>'], ['ADPBV','ASDPV','>'],
                ];
                for (const [a,b,op] of pairs){
                    if (op==='>' && !gt(p[a],p[b])) return fail(`${a} must be greater than ${b}`, a);
                }

                if(p.NCHPCA_1!=null && p.NCHPCA_2!=null && p.NCHPCA_3!=null){
                    if(!(p.NCHPCA_1>p.NCHPCA_2 && p.NCHPCA_2>p.NCHPCA_3)) return fail('Charging gear currents must be descending (1>2>3)', 'NCHPCA_1');
                }
                if(p.NCHPDT_1!=null && p.NCHPDT_2!=null && p.NCHPDT_3!=null){
                    if(!(p.NCHPDT_1<p.NCHPDT_2 && p.NCHPDT_2<p.NCHPDT_3)) return fail('Charging durations must be ascending (1<2<3)', 'NCHPDT_1');
                }
                if(p.NDHPCA_1!=null && p.NDHPCA_2!=null && p.NDHPCA_3!=null){
                    if(!(p.NDHPCA_1>p.NDHPCA_2 && p.NDHPCA_2>p.NDHPCA_3)) return fail('Discharge gear currents must be descending (1>2>3)', 'NDHPCA_1');
                }
                if(p.NDHPDT_1!=null && p.NDHPDT_2!=null && p.NDHPDT_3!=null){
                    if(!(p.NDHPDT_1<p.NDHPDT_2 && p.NDHPDT_2<p.NDHPDT_3)) return fail('Discharge durations must be ascending (1<2<3)', 'NDHPDT_1');
                }

                for(let i=1;i<=5;i++){
                    const OP = p[`NPCMOP_${i}`], OR = p[`NPCMOR_${i}`], UP = p[`NPCMUP_${i}`], UR = p[`NPCMUR_${i}`];
                    if (OP!=null && OR!=null && !(OP>OR)) return fail(`Temp${i}: over-protect must be > over-recovery`, `NPCMOP_${i}`);
                    if (UR!=null && UP!=null && !(UR>UP)) return fail(`Temp${i}: under-recovery must be > under-protect`, `NPCMUR_${i}`);
                }
                return {ok:true};
            }
            function markInvalid(fieldKey, scope){
                const input = scope ? scope.querySelector(`[name="params[${fieldKey}]"]`) : document.querySelector(`[name="params[${fieldKey}]"]`);
                if (input){ input.classList.add('is-invalid'); input.addEventListener('input', ()=>input.classList.remove('is-invalid'), {once:true}); }
            }
            $('#btn-validate').addEventListener('click', ()=>{
                $$('.is-invalid').forEach(el=>el.classList.remove('is-invalid'));
                const p = vmap(baseParamsWrap);
                let res = validateParams(p);
                if (!res.ok){ markInvalid(res.field, baseParamsWrap); makeToast(`Validation failed: ${res.msg}`, 'danger'); return; }

                if (modePer.checked){
                    const panels = $$('.accordion-item', overridesAcc);
                    for (const panel of panels){
                        const ov = {};
                        $$('.ov-input', panel).forEach(inp=>{
                            const key = inp.name.match(/\[([^\]]+)\]$/)?.[1];
                            if (key) ov[key] = (inp.value === '' ? null : Number(inp.value));
                        });
                        const filled = Object.values(ov).filter(v=>v!=null).length;
                        if (filled >= 2){
                            res = validateParams(ov);
                            if (!res.ok){
                                const bad = panel.querySelector(`[name$="[${res.field}]"]`);
                                if (bad){
                                    bad.classList.add('is-invalid');
                                    bad.addEventListener('input', ()=>bad.classList.remove('is-invalid'), {once:true});
                                }
                                makeToast(`Override validation failed: ${res.msg}`, 'danger');
                                return;
                            }
                        }
                    }
                }
                makeToast('Validation passed ✓', 'success');
            });

            // Auto-build param toggle
            function syncAutoParamUI(){
                const on = autoParam.checked;
                autoParamHidden.value = on ? '1' : '0';
                paramInput.disabled = on;
                paramInput.placeholder = on
                    ? 'Hex blob or vendor-defined string (manual mode only)'
                    : 'Paste the vendor hex blob here';
            }
            autoParam.addEventListener('change', syncAutoParamUI);
            syncAutoParamUI();

            // Command presets -> fill cmd input
            cmdPresets.addEventListener('change', ()=>{
                const v = cmdPresets.value || '';
                if (v) {
                    cmdInput.value = v;
                    cmdInput.dispatchEvent(new Event('input', {bubbles:true}));
                }
            });

            // Preview
            const fillBaseFromSeting = (setingObj) => {
                if (!setingObj || typeof setingObj !== 'object') return;
                // Fill known fields if present in Seting
                $$('.param-input', baseParamsWrap).forEach(inp=>{
                    const key = inp.name.match(/\[(.+)\]/)?.[1];
                    if (!key) return;
                    if (setingObj[key] !== undefined && inp.value === '') {
                        inp.value = String(setingObj[key]);
                    }
                });
            };

            $('#preview-btn').addEventListener('click', async ()=>{
                if (!endpointPreview){ makeToast('Preview endpoint unavailable.', 'danger'); return; }
                if (selected.length===0) return;
                previewBtn.disabled = true; previewBtn.innerText = 'Loading...';

                try{
                    const res = await fetch(endpointPreview, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ batteries: selected.join(',') })
                    });
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    const data = await res.json();
                    if (!data.ok) throw new Error(data.message || 'Preview failed');

                    const rows = [];
                    const vendors = data.data || {};
                    Object.keys(vendors).forEach(vendor=>{
                        const vdata = vendors[vendor] || {};
                        const statuses = (vdata.status||[]).flatMap(s=>{
                            const list = (s?.data)||s?.Data||s?.list||[];
                            return Array.isArray(list)? list : [];
                        });
                        const statusByNo = {};
                        statuses.forEach(item=>{
                            const number = item.Number || item.number || item.mac_id || item.MacID;
                            if(!number) return;
                            statusByNo[number] = item;
                        });
                        const settings = vdata.settings || {};
                        Object.keys(settings).forEach(no=>{
                            rows.push({ vendor, number: no, status: statusByNo[no]||{}, settings: settings[no]||{} });
                        });
                    });

                    previewTbody.innerHTML = rows.map(r=>{
                        const s = r.status || {};
                        const online = (s.online === true || s.Online === true);
                        const on = online ? '🟢 Online' : '🟡';
                        const last = s.BMS_DateTime || s.heart_time || '-';
                        const ch  = (s.CHON ?? s.ChargeOn ?? s.charge ?? null);
                        const dh  = (s.DHON ?? s.DisOn   ?? s.dis    ?? null);
                        const chTxt = (ch===null?'-':(ch?'ON':'OFF'));
                        const dhTxt = (dh===null?'-':(dh?'ON':'OFF'));
                        const pv  = s.BetteryV_All || s.PackV || s.PackVoltage || '-';
                        const soc = (s.SOC ?? s.Soc ?? '-');
                        return `
<tr>
  <td><code>${r.number}</code><div class="text-muted small">${r.vendor}</div></td>
  <td>${on}</td>
  <td class="small">${last}</td>
  <td>${chTxt}</td>
  <td>${dhTxt}</td>
  <td>${pv}</td>
  <td>${soc}</td>
</tr>`;
                    }).join('');

                    // Convenience: if we have exactly one selected device and we received its Seting, prefill base fields.
                    if (selected.length === 1) {
                        const match = rows.find(r => r.number === selected[0]);
                        if (match && match.settings && typeof match.settings === 'object') {
                            fillBaseFromSeting(match.settings);
                        }
                    }

                    previewCard.classList.remove('d-none');
                    previewCard.setAttribute('aria-hidden', 'false');
                    makeToast('Preview loaded.', 'success');

                } catch(err){
                    console.error(err);
                    makeToast(`Preview error: ${err.message}`, 'danger');
                } finally {
                    previewBtn.disabled = false;
                    previewBtn.innerText = '🔍 Preview status & settings';
                }
            });

            // Submit spinner
            form.addEventListener('submit', ()=>{
                if (!canSubmit()) {
                    makeToast('Pick at least one battery and provide a command (cmd).', 'danger');
                    return;
                }
                submitBtn.disabled = true;
                submitText.classList.add('d-none');
                submitSpinner.classList.remove('d-none');
            });

            // Live submit-state updates
            cmdInput.addEventListener('input', updateSubmitState);

            // Init
            renderBadges();
            toggleOverrides();
            updateSubmitState();

            // Session toast (from controller)
            @if(session('response'))
            (()=>{
                const response = @json(session('response'));
                const ok = (String(response.errorCode) === '200') || (response.success === true || response.success === 'true');
                makeToast((response.errorDescribe ?? 'No description'), ok ? 'success' : 'danger');
            })();
            @endif

        })();
    </script>
@endpush
