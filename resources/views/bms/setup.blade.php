@extends('layouts.app')

@section('content')
    <div class="content">
        <h3 class="text-center mb-4">🔧 Configure BMS Parameters</h3>

        <form method="POST" action="{{ route('bms.send') }}" id="bms-form" style="max-width: 900px; margin: auto;">
            @csrf

            {{-- Battery Selection --}}
            <div class="mb-4">
                <label class="form-label">📦 Select Batteries (at least 2)</label>
                <select id="battery-select" class="form-control">
                    <option value="">-- Select a battery --</option>
                    @foreach($batteries as $battery)
                        <option value="{{ $battery->mac_id }}">{{ $battery->mac_id }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Selected Batteries Preview --}}
            <div class="mb-4">
                <label class="form-label">✅ Selected Batteries</label>
                <div id="selected-batteries" class="p-3 bg-light border rounded mb-2" style="min-height: 50px;">
                    <em>No batteries selected</em>
                </div>
                <button type="button" id="clear-batteries" class="btn btn-danger btn-sm" style="display:none;">🗑️ Clear All</button>
            </div>

            {{-- Hidden input to submit selected batteries --}}
            <input type="hidden" name="batteries" id="batteries-hidden">

            {{-- Parameters --}}
            <h5 class="text-center mb-3">⚙️ BMS Parameters</h5>

            @php
                $params = [
                    'CPBV', 'SRBV', 'DRBV', 'DPBV', 'ASCPV', 'ACPBV', 'ASRBV', 'ADRBV', 'ADPBV', 'ASDPV',
                    'SSPV', 'BCRL', 'EMST', 'ESBV', 'LRLAV',
                    'NCHPCA_1', 'NCHPCA_2', 'NCHPCA_3', 'NCHPDT_1', 'NCHPDT_2', 'NCHPDT_3', 'NCHPRT_1', 'NCHPRT_2', 'NCHPRT_3',
                    'NDHPCA_1', 'NDHPCA_2', 'NDHPCA_3', 'NDHPDT_1', 'NDHPDT_2', 'NDHPDT_3', 'NDHPRT_1', 'NDHPRT_2', 'NDHPRT_3',
                    'NPCMOP_1', 'NPCMOR_1', 'NPCMUP_1', 'NPCMUR_1',
                    'NPCMOP_2', 'NPCMOR_2', 'NPCMUP_2', 'NPCMUR_2',
                    'NPCMOP_3', 'NPCMOR_3', 'NPCMUP_3', 'NPCMUR_3',
                    'NPCMOP_4', 'NPCMOR_4', 'NPCMUP_4', 'NPCMUR_4',
                    'NPCMOP_5', 'NPCMOR_5', 'NPCMUP_5', 'NPCMUR_5'
                ];
            @endphp

            <div class="row">
                @foreach($params as $param)
                    <div class="col-md-6 mb-2">
                        <label>{{ $param }}</label>
                        <input type="text" name="params[{{ $param }}]" class="form-control">
                    </div>
                @endforeach
            </div>

            {{-- Submit Button --}}
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary" id="submit-button">
                    <span id="submit-text">✅ Send BMS Commands</span>
                    <span id="submit-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </form>
    </div>

    {{-- Toast container --}}
    <div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    {{-- JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('battery-select');
            const display = document.getElementById('selected-batteries');
            const hiddenInput = document.getElementById('batteries-hidden');
            const clearButton = document.getElementById('clear-batteries');
            const submitButton = document.getElementById('submit-button');
            const submitText = document.getElementById('submit-text');
            const submitSpinner = document.getElementById('submit-spinner');

            let selectedBatteries = [];

            function renderSelectedBatteries() {
                display.innerHTML = '';

                if (selectedBatteries.length === 0) {
                    display.innerHTML = '<em>No batteries selected</em>';
                    clearButton.style.display = 'none';
                } else {
                    selectedBatteries.forEach((mac, index) => {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-success me-1 mb-1';
                        badge.style.cursor = 'pointer';
                        badge.innerHTML = `${mac} <small style="cursor:pointer;">❌</small>`;
                        badge.addEventListener('click', function () {
                            selectedBatteries.splice(index, 1);
                            renderSelectedBatteries();
                        });
                        display.appendChild(badge);
                    });
                    clearButton.style.display = 'inline-block';
                }

                hiddenInput.value = selectedBatteries.join(',');
            }

            select.addEventListener('change', function () {
                const mac = select.value;
                if (mac && !selectedBatteries.includes(mac)) {
                    selectedBatteries.push(mac);
                    renderSelectedBatteries();
                }
                select.value = '';
            });

            clearButton.addEventListener('click', function () {
                selectedBatteries = [];
                renderSelectedBatteries();
            });

            renderSelectedBatteries();

            // Show spinner on form submit
            const form = document.getElementById('bms-form');
            form.addEventListener('submit', function () {
                submitButton.disabled = true;
                submitText.classList.add('d-none');
                submitSpinner.classList.remove('d-none');
            });

            // Show Toast if response exists
            @if(session('response'))
            const response = @json(session('response'));
            const isSuccess = response.errorCode == 0;

            const toastHtml = `
                <div class="toast align-items-center text-white ${isSuccess ? 'bg-success' : 'bg-danger'} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong>${isSuccess ? '✅ Success:' : '❌ Error:'}</strong> ${response.errorDescribe ?? 'No description available'}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;

            const container = document.getElementById('toast-container');
            container.innerHTML = toastHtml;

            const toastElement = container.querySelector('.toast');
            const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
            toast.show();
            @endif
        });
    </script>

    {{-- Bootstrap JS (required for Toasts!) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection
