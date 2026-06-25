<style>
    .vital-input:focus { border-color: var(--secondary) !important; }
    .vital-input { transition: border-color var(--duration-fast) var(--ease-out); }
</style>
<div id="vitalFormPanel" style="display: none;">
    <div style="background: var(--card-bg); border: 1px solid var(--border-hairline); border-radius: var(--radius); overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-md) var(--space-lg); background: var(--panel-bg); border-bottom: 1px solid var(--border-hairline);">
            <h5 style="margin: 0; font-size: var(--text-base); font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: var(--space-sm);">
                <i class="fas fa-heartbeat" style="color: var(--secondary);"></i>
                <span data-i18n="vitalsForm">{{ __('messages.vitalsForm') }}</span>
                <span id="vitalPatientName" style="font-weight: 400; color: var(--text-muted); font-size: var(--text-sm);"></span>
            </h5>
            <button type="button" id="vitalFormClose" style="background: none; border: none; color: var(--text-muted); font-size: var(--text-lg); cursor: pointer; padding: 0; line-height: 1;">&times;</button>
        </div>

        <form id="vitalsForm" method="POST" style="padding: var(--space-lg);">
            @csrf
            <input type="hidden" id="vitalAppointmentId" name="appointment_id" value="">

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-lg);">
                <div>
                    <label for="temperature" style="display: block; font-size: var(--text-xs); font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: var(--space-xs);">{{ __('messages.temperature') }} <span style="color: var(--danger);">*</span></label>
                    <input type="number" step="0.1" id="temperature" name="temperature" value="{{ old('temperature') }}" required min="30" max="45" placeholder="36.6" class="vital-input" style="width: 100%; padding: var(--space-sm) var(--space-md); background: var(--input-bg); border: 1px solid var(--border-hairline); border-radius: var(--radius-sm); color: var(--text-primary); font-size: var(--text-sm); outline: none;">
                </div>

                <div>
                    <label for="pulse" style="display: block; font-size: var(--text-xs); font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: var(--space-xs);">{{ __('messages.heartRate') }} <span style="color: var(--danger);">*</span></label>
                    <input type="number" id="pulse" name="pulse" value="{{ old('pulse') }}" required min="30" max="250" placeholder="72" class="vital-input" style="width: 100%; padding: var(--space-sm) var(--space-md); background: var(--input-bg); border: 1px solid var(--border-hairline); border-radius: var(--radius-sm); color: var(--text-primary); font-size: var(--text-sm); outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: var(--text-xs); font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: var(--space-xs);">{{ __('messages.bloodPressure') }} <span style="color: var(--danger);">*</span></label>
                    <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 2px; align-items: center;">
                        <input type="number" name="bp_systolic" value="{{ old('bp_systolic') }}" required min="50" max="250" placeholder="120" class="vital-input" style="width: 100%; padding: var(--space-sm) var(--space-md); background: var(--input-bg); border: 1px solid var(--border-hairline); border-radius: var(--radius-sm); color: var(--text-primary); font-size: var(--text-sm); outline: none; text-align: center;">
                        <span style="color: var(--text-muted); font-size: var(--text-sm); padding: 0 2px;">/</span>
                        <input type="number" name="bp_diastolic" value="{{ old('bp_diastolic') }}" required min="30" max="150" placeholder="80" class="vital-input" style="width: 100%; padding: var(--space-sm) var(--space-md); background: var(--input-bg); border: 1px solid var(--border-hairline); border-radius: var(--radius-sm); color: var(--text-primary); font-size: var(--text-sm); outline: none; text-align: center;">
                    </div>
                </div>

                <div>
                    <label for="respiratory_rate" style="display: block; font-size: var(--text-xs); font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: var(--space-xs);">{{ __('messages.respiratoryRate') }}</label>
                    <input type="number" id="respiratory_rate" name="respiratory_rate" value="{{ old('respiratory_rate') }}" min="10" max="60" placeholder="16" class="vital-input" style="width: 100%; padding: var(--space-sm) var(--space-md); background: var(--input-bg); border: 1px solid var(--border-hairline); border-radius: var(--radius-sm); color: var(--text-primary); font-size: var(--text-sm); outline: none;">
                </div>

                <div>
                    <label for="weight" style="display: block; font-size: var(--text-xs); font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: var(--space-xs);">{{ __('messages.weight') }} <span style="color: var(--danger);">*</span></label>
                    <input type="number" step="0.01" id="weight" name="weight" value="{{ old('weight') }}" required min="1" max="500" placeholder="70.0" class="vital-input" style="width: 100%; padding: var(--space-sm) var(--space-md); background: var(--input-bg); border: 1px solid var(--border-hairline); border-radius: var(--radius-sm); color: var(--text-primary); font-size: var(--text-sm); outline: none;">
                </div>

                <div>
                    <label for="height" style="display: block; font-size: var(--text-xs); font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: var(--space-xs);">{{ __('messages.height') }}</label>
                    <input type="number" step="0.01" id="height" name="height" value="{{ old('height') }}" min="10" max="300" placeholder="175.0" class="vital-input" style="width: 100%; padding: var(--space-sm) var(--space-md); background: var(--input-bg); border: 1px solid var(--border-hairline); border-radius: var(--radius-sm); color: var(--text-primary); font-size: var(--text-sm); outline: none;">
                </div>

                <div>
                    <label for="oxygen_saturation" style="display: block; font-size: var(--text-xs); font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: var(--space-xs);">{{ __('messages.oxygenSaturation') }}</label>
                    <input type="number" id="oxygen_saturation" name="oxygen_saturation" value="{{ old('oxygen_saturation') }}" min="50" max="100" placeholder="98" class="vital-input" style="width: 100%; padding: var(--space-sm) var(--space-md); background: var(--input-bg); border: 1px solid var(--border-hairline); border-radius: var(--radius-sm); color: var(--text-primary); font-size: var(--text-sm); outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: var(--text-xs); font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: var(--space-xs);">BMI</label>
                    <div id="bmiDisplay" style="width: 100%; padding: var(--space-sm) var(--space-md); background: var(--panel-bg); border: 1px solid var(--border-hairline); border-radius: var(--radius-sm); color: var(--text-muted); font-size: var(--text-sm); line-height: 2.2;">--</div>
                </div>

                <div></div>
            </div>

            <div style="margin-top: var(--space-lg);">
                <label for="notes" style="display: block; font-size: var(--text-xs); font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: var(--space-xs);">{{ __('messages.medicalNotes') }}</label>
                <textarea id="notes" name="notes" rows="2" class="vital-input" style="width: 100%; padding: var(--space-sm) var(--space-md); background: var(--input-bg); border: 1px solid var(--border-hairline); border-radius: var(--radius-sm); color: var(--text-primary); font-size: var(--text-sm); outline: none; resize: vertical;">{{ old('notes') }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: var(--space-md); margin-top: var(--space-lg);">
                <button type="button" id="vitalFormCancel" style="padding: var(--space-sm) var(--space-lg); background: var(--panel-bg); border: 1px solid var(--border-hairline); border-radius: var(--radius-sm); color: var(--text-secondary); font-size: var(--text-sm); font-weight: 500; cursor: pointer; transition: background var(--duration-fast) var(--ease-out);" onmouseover="this.style.background='var(--input-bg)'" onmouseout="this.style.background='var(--panel-bg)'">{{ __('messages.cancel') }}</button>
                <button type="submit" style="padding: var(--space-sm) var(--space-lg); background: var(--secondary); border: none; border-radius: var(--radius-sm); color: #fff; font-size: var(--text-sm); font-weight: 600; cursor: pointer; transition: opacity var(--duration-fast) var(--ease-out); display: inline-flex; align-items: center; gap: var(--space-xs);" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                    <i class="fas fa-check-circle"></i> {{ __('messages.approveAndSend') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const weightInput = document.getElementById('weight');
    const heightInput = document.getElementById('height');
    const bmiDisplay = document.getElementById('bmiDisplay');

    function computeBMI() {
        const w = parseFloat(weightInput.value);
        const h = parseFloat(heightInput.value);
        if (w > 0 && h > 0) {
            const hM = h / 100;
            const bmi = w / (hM * hM);
            bmiDisplay.textContent = bmi.toFixed(1);
            if (bmi < 18.5) bmiDisplay.style.color = 'var(--info)';
            else if (bmi < 25) bmiDisplay.style.color = 'var(--success)';
            else if (bmi < 30) bmiDisplay.style.color = 'var(--warning)';
            else bmiDisplay.style.color = 'var(--danger)';
        } else {
            bmiDisplay.textContent = '--';
            bmiDisplay.style.color = 'var(--text-muted)';
        }
    }

    if (weightInput) weightInput.addEventListener('input', computeBMI);
    if (heightInput) heightInput.addEventListener('input', computeBMI);
})();
</script>
