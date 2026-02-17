<script>
    window.initialPreset = @json($preset ?? null);
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('lensPowerTable');
        const cells = table.querySelectorAll('.selectable-cell');
        const colHeaders = table.querySelectorAll('.selectable-col-header');
        const rowHeaders = table.querySelectorAll('.selectable-row-header');
        let selectedCells = new Set();
        let lastSelectedCell = null;
        let isMouseDown = false;
        let startCell = null;

        // ===================== Core Functions =====================

        function selectCell(cell) {
            if (!cell || selectedCells.has(cell)) return;
            cell.classList.add('cell-selected');
            selectedCells.add(cell);
        }

        function deselectCell(cell) {
            if (!cell) return;
            cell.classList.remove('cell-selected');
            selectedCells.delete(cell);
        }

        function toggleCell(cell) {
            if (selectedCells.has(cell)) {
                deselectCell(cell);
            } else {
                selectCell(cell);
            }
        }

        function selectRange(from, to) {
            if (!from || !to) return;
            const r1 = +from.dataset.row, c1 = +from.dataset.col;
            const r2 = +to.dataset.row,   c2 = +to.dataset.col;
            const minR = Math.min(r1, r2), maxR = Math.max(r1, r2);
            const minC = Math.min(c1, c2), maxC = Math.max(c1, c2);
            cells.forEach(cell => {
                const r = +cell.dataset.row, c = +cell.dataset.col;
                if (r >= minR && r <= maxR && c >= minC && c <= maxC) selectCell(cell);
            });
        }

        function clearAll() {
            selectedCells.forEach(cell => cell.classList.remove('cell-selected'));
            selectedCells.clear();
            lastSelectedCell = null;
            refreshUI();
        }

        function selectAll() {
            cells.forEach(cell => selectCell(cell));
            refreshUI();
        }

        // ============= Row / Column Toggle =============

        function getCellsByCol(col) {
            return Array.from(cells).filter(c => +c.dataset.col === col);
        }

        function getCellsByRow(row) {
            return Array.from(cells).filter(c => +c.dataset.row === row);
        }

        function toggleColumn(col) {
            const group = getCellsByCol(col);
            const allSelected = group.every(c => selectedCells.has(c));
            group.forEach(c => allSelected ? deselectCell(c) : selectCell(c));
        }

        function toggleRow(row) {
            const group = getCellsByRow(row);
            const allSelected = group.every(c => selectedCells.has(c));
            group.forEach(c => allSelected ? deselectCell(c) : selectCell(c));
        }

        // ============= UI Refresh =============

        function refreshUI() {
            const count = selectedCells.size;
            const badge = document.getElementById('selectedCount');
            const num   = document.getElementById('countNum');
            const card  = document.getElementById('selectedInfoCard');

            if (count > 0) {
                badge.style.display = '';
                num.textContent = count;
            } else {
                badge.style.display = 'none';
            }

            const stickyCount = document.getElementById('stickyCount');
            if (stickyCount) {
                stickyCount.textContent = count > 0 ? count + ' خلية محددة' : '0 خلية محددة';
                stickyCount.className = count > 0 ? 'badge badge-light-primary fs-7 px-3 py-2' : 'badge badge-light-secondary fs-7 px-3 py-2';
            }

            colHeaders.forEach(h => {
                const col = +h.dataset.col;
                const group = getCellsByCol(col);
                h.classList.toggle('header-all-selected', group.length > 0 && group.every(c => selectedCells.has(c)));
            });
            rowHeaders.forEach(h => {
                const row = +h.dataset.row;
                const group = getCellsByRow(row);
                h.classList.toggle('header-all-selected', group.length > 0 && group.every(c => selectedCells.has(c)));
            });

            if (count === 0) {
                card.style.display = 'none';
                return;
            }

            card.style.display = '';
            const infoDiv = document.getElementById('selectedInfo');

            const sphSet = new Set(), cylSet = new Set();
            const items = [];
            selectedCells.forEach(cell => {
                const sph = parseFloat(cell.dataset.sph);
                const cyl = parseFloat(cell.dataset.cyl);
                sphSet.add(sph);
                cylSet.add(cyl);
                items.push({ sph, cyl });
            });

            const sphArr = [...sphSet].sort((a,b) => b - a);
            const cylArr = [...cylSet].sort((a,b) => b - a);
            const fmt = v => (v >= 0 ? '+' : '') + v.toFixed(2);

            let html = `
                <div class="row g-3 mb-4">
                    <div class="col-auto">
                        <div class="border rounded px-3 py-2 text-center">
                            <div class="text-muted fs-8">محدد</div>
                            <div class="fw-bold text-primary fs-5">${count}</div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="border rounded px-3 py-2 text-center">
                            <div class="text-muted fs-8">نطاق SPH</div>
                            <div class="fw-bold fs-7">${fmt(Math.min(...sphArr))} → ${fmt(Math.max(...sphArr))}</div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="border rounded px-3 py-2 text-center">
                            <div class="text-muted fs-8">نطاق CYL</div>
                            <div class="fw-bold fs-7">${fmt(Math.min(...cylArr))} → ${fmt(Math.max(...cylArr))}</div>
                        </div>
                    </div>
                </div>
                <div class="values-grid">`;

            items.sort((a, b) => b.sph - a.sph || b.cyl - a.cyl);
            items.forEach(item => {
                html += `<span class="value-chip"><strong>${fmt(item.sph)}</strong> / ${fmt(item.cyl)}</span>`;
            });

            html += `</div>`;
            infoDiv.innerHTML = html;
        }

        // ============= Event Listeners — Cells =============

        // ============= Crosshair Highlight =============

        let hoveredRow = null;
        let hoveredCol = null;

        function setCrosshair(row, col) {
            if (row === hoveredRow && col === hoveredCol) return;
            clearCrosshair();
            hoveredRow = row;
            hoveredCol = col;
            if (row === null && col === null) return;

            if (row !== null) {
                const rh = table.querySelector(`.selectable-row-header[data-row="${row}"]`);
                if (rh) rh.classList.add('header-hover');
                cells.forEach(c => { if (+c.dataset.row === row) c.classList.add('cell-row-hover'); });
            }
            if (col !== null) {
                const ch = table.querySelector(`.selectable-col-header[data-col="${col}"]`);
                if (ch) ch.classList.add('header-hover');
                cells.forEach(c => { if (+c.dataset.col === col) c.classList.add('cell-col-hover'); });
            }
        }

        function clearCrosshair() {
            if (hoveredRow === null && hoveredCol === null) return;
            table.querySelectorAll('.header-hover').forEach(el => el.classList.remove('header-hover'));
            table.querySelectorAll('.cell-row-hover').forEach(el => el.classList.remove('cell-row-hover'));
            table.querySelectorAll('.cell-col-hover').forEach(el => el.classList.remove('cell-col-hover'));
            hoveredRow = null;
            hoveredCol = null;
        }

        document.getElementById('tableWrapper').addEventListener('mouseleave', clearCrosshair);

        cells.forEach(cell => {
            cell.addEventListener('mousedown', function(e) {
                if (e.button !== 0) return;
                e.preventDefault();
                isMouseDown = true;
                startCell = cell;
            });

            cell.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (e.shiftKey && lastSelectedCell) {
                    selectRange(lastSelectedCell, cell);
                } else {
                    toggleCell(cell);
                }
                lastSelectedCell = cell;
                refreshUI();
            });

            cell.addEventListener('mouseenter', function() {
                setCrosshair(+cell.dataset.row, +cell.dataset.col);
                if (isMouseDown && startCell) {
                    selectRange(startCell, cell);
                    refreshUI();
                }
            });
        });

        document.addEventListener('mouseup', () => { isMouseDown = false; startCell = null; });

        // ============= Event Listeners — Headers =============

        colHeaders.forEach(h => {
            h.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleColumn(+h.dataset.col);
                refreshUI();
            });
        });

        rowHeaders.forEach(h => {
            h.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleRow(+h.dataset.row);
                refreshUI();
            });
        });

        // ============= Keyboard Navigation =============

        document.addEventListener('keydown', function(e) {
            if (!lastSelectedCell) return;
            const row = +lastSelectedCell.dataset.row;
            const col = +lastSelectedCell.dataset.col;
            let target = null;

            switch(e.key) {
                case 'ArrowUp':    target = table.querySelector(`[data-row="${row-1}"][data-col="${col}"]`); break;
                case 'ArrowDown':  target = table.querySelector(`[data-row="${row+1}"][data-col="${col}"]`); break;
                case 'ArrowLeft':  target = table.querySelector(`[data-row="${row}"][data-col="${col-1}"]`); break;
                case 'ArrowRight': target = table.querySelector(`[data-row="${row}"][data-col="${col+1}"]`); break;
                default: return;
            }

            if (target) {
                e.preventDefault();
                if (e.shiftKey) {
                    selectRange(lastSelectedCell, target);
                } else {
                    toggleCell(target);
                }
                lastSelectedCell = target;
                refreshUI();
                target.scrollIntoView({ block: 'nearest', inline: 'nearest' });
            }
        });

        // ============= Toolbar Buttons =============

        document.getElementById('clearSelection').addEventListener('click', clearAll);
        document.getElementById('selectAll').addEventListener('click', () => { selectAll(); });

        // ============= Formula Selection =============

        const totalCheckbox = document.getElementById('fTotalEnabled');
        const totalInputs = document.getElementById('totalInputs');
        const inputTotalFrom = document.getElementById('fTotalFrom');
        const inputTotalTo = document.getElementById('fTotalTo');

        function setTotalInputsState(enabled) {
            inputTotalFrom.disabled = !enabled;
            inputTotalTo.disabled = !enabled;
            totalInputs.style.opacity = enabled ? '1' : '0.5';
        }

        totalCheckbox.addEventListener('change', function() {
            setTotalInputsState(this.checked);
        });
        setTotalInputsState(totalCheckbox.checked);

        function getFormulaMatches() {
            const sphFrom = document.getElementById('fSphFrom').value;
            const sphTo   = document.getElementById('fSphTo').value;
            const cylFrom = document.getElementById('fCylFrom').value;
            const cylTo   = document.getElementById('fCylTo').value;
            const totalEnabled = totalCheckbox.checked;
            const totalFrom = document.getElementById('fTotalFrom').value;
            const totalTo   = document.getElementById('fTotalTo').value;

            if (sphFrom === '' && sphTo === '' && cylFrom === '' && cylTo === '') {
                return { error: 'يجب إدخال نطاق SPH أو CYL على الأقل' };
            }

            const minSph = sphFrom !== '' ? parseFloat(sphFrom) : -Infinity;
            const maxSph = sphTo   !== '' ? parseFloat(sphTo)   : Infinity;
            const minCyl = cylFrom !== '' ? parseFloat(cylFrom) : -Infinity;
            const maxCyl = cylTo   !== '' ? parseFloat(cylTo)   : Infinity;

            const sMin = Math.min(minSph, maxSph), sMax = Math.max(minSph, maxSph);
            const cMin = Math.min(minCyl, maxCyl), cMax = Math.max(minCyl, maxCyl);

            let tMin = -Infinity, tMax = Infinity;
            if (totalEnabled && (totalFrom !== '' || totalTo !== '')) {
                tMin = totalFrom !== '' ? parseFloat(totalFrom) : -Infinity;
                tMax = totalTo   !== '' ? parseFloat(totalTo)   : Infinity;
                if (tMin > tMax) { const tmp = tMin; tMin = tMax; tMax = tmp; }
            }

            const matched = [];
            cells.forEach(cell => {
                const sph = parseFloat(cell.dataset.sph);
                const cyl = parseFloat(cell.dataset.cyl);
                const total = Math.round((sph + cyl) * 100) / 100;

                if (sph >= sMin && sph <= sMax && cyl >= cMin && cyl <= cMax) {
                    if (!totalEnabled || (total >= tMin && total <= tMax)) {
                        matched.push(cell);
                    }
                }
            });

            return { matched, sMin, sMax, cMin, cMax, tMin, tMax, totalEnabled };
        }

        function showFormulaResult(count, mode) {
            const el = document.getElementById('formulaResult');
            const labels = { apply: 'تم تحديد', add: 'تمت إضافة', subtract: 'تم حذف' };
            el.style.display = '';
            el.innerHTML = `<span class="badge bg-light-${mode === 'subtract' ? 'danger' : 'success'} text-${mode === 'subtract' ? 'danger' : 'success'} fs-7 px-3 py-2">
                <i class="fas fa-${mode === 'subtract' ? 'minus' : 'check'}-circle me-1"></i>
                ${labels[mode]} <strong>${count}</strong> خلية
            </span>`;
            setTimeout(() => { el.style.display = 'none'; }, 3000);
        }

        document.getElementById('applyFormula').addEventListener('click', function() {
            const result = getFormulaMatches();
            if (result.error) { alert(result.error); return; }
            clearAll();
            result.matched.forEach(cell => selectCell(cell));
            refreshUI();
            showFormulaResult(result.matched.length, 'apply');
        });

        document.getElementById('addFormula').addEventListener('click', function() {
            const result = getFormulaMatches();
            if (result.error) { alert(result.error); return; }
            result.matched.forEach(cell => selectCell(cell));
            refreshUI();
            showFormulaResult(result.matched.length, 'add');
        });

        document.getElementById('subtractFormula').addEventListener('click', function() {
            const result = getFormulaMatches();
            if (result.error) { alert(result.error); return; }
            let removed = 0;
            result.matched.forEach(cell => {
                if (selectedCells.has(cell)) { deselectCell(cell); removed++; }
            });
            refreshUI();
            showFormulaResult(removed, 'subtract');
        });

        document.getElementById('exportSelected').addEventListener('click', function() {
            if (selectedCells.size === 0) {
                alert('لم يتم تحديد أي خلايا!');
                return;
            }
            const items = [];
            selectedCells.forEach(cell => {
                items.push({ sph: parseFloat(cell.dataset.sph), cyl: parseFloat(cell.dataset.cyl) });
            });
            items.sort((a, b) => b.sph - a.sph || b.cyl - a.cyl);
            const json = JSON.stringify(items, null, 2);

            navigator.clipboard.writeText(json).then(() => {
                const btn = document.getElementById('exportSelected');
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check me-1"></i> تم النسخ!';
                btn.classList.remove('btn-light-success');
                btn.classList.add('btn-success');
                setTimeout(() => { btn.innerHTML = orig; btn.classList.remove('btn-success'); btn.classList.add('btn-light-success'); }, 2000);
            }).catch(() => {
                prompt('انسخ القيم:', json);
            });
        });

        // ============= Save =============

        function getSelectedValuesAsArray() {
            const items = [];
            selectedCells.forEach(cell => {
                items.push({ sph: parseFloat(cell.dataset.sph), cyl: parseFloat(cell.dataset.cyl) });
            });
            return items.sort((a, b) => b.sph - a.sph || b.cyl - a.cyl);
        }

        function findCellBySphCyl(sph, cyl) {
            const r = (v) => Math.round(v * 100) / 100;
            for (const cell of cells) {
                if (r(parseFloat(cell.dataset.sph)) === r(sph) && r(parseFloat(cell.dataset.cyl)) === r(cyl)) {
                    return cell;
                }
            }
            return null;
        }

        function showPresetMessage(text, isError) {
            const el = document.getElementById('presetMessage');
            if (!el) return;
            el.style.display = 'inline-flex';
            const color = isError ? '#f1416c' : '#50cd89';
            el.className = 'mb-0 d-inline-flex align-items-center gap-1 fs-7';
            el.style.color = color;
            const icon = isError ? 'fa-exclamation-circle' : 'fa-check-circle';
            el.innerHTML = '<i class="fas ' + icon + '"></i> ' + text;
            setTimeout(() => { el.style.display = 'none'; }, 4500);
        }

        function setSaveButtonLoading(btn, loading) {
            if (!btn) return;
            const icon = btn.querySelector('i');
            if (loading) {
                btn.disabled = true;
                if (icon) { icon.className = 'fas fa-spinner fa-spin me-2'; }
            } else {
                btn.disabled = false;
                if (icon) { icon.className = 'fas fa-save me-2'; }
            }
        }

        document.getElementById('btnSave').addEventListener('click', function() {
            const values = getSelectedValuesAsArray();
            const btn = this;
            const isEdit = window.initialPreset && window.initialPreset.id;
            const nameInput = document.getElementById('presetName');

            if (values.length === 0) {
                showPresetMessage('حدد خلايا في الجدول أولاً.', true);
                return;
            }

            if (!isEdit) {
                const name = (nameInput ? nameInput.value : '').trim();
                if (!name) {
                    showPresetMessage('أدخل اسم المحفوظة أعلاه.', true);
                    if (nameInput) {
                        nameInput.classList.add('is-invalid');
                        nameInput.focus();
                        nameInput.addEventListener('input', function handler() {
                            nameInput.classList.remove('is-invalid');
                            nameInput.removeEventListener('input', handler);
                        });
                    }
                    return;
                }
            }

            setSaveButtonLoading(btn, true);

            if (isEdit) {
                const url = '{{ url("admin/multi-select-table") }}/' + window.initialPreset.id;
                fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ values })
                })
                .then(r => r.json())
                .then(data => {
                    setSaveButtonLoading(btn, false);
                    if (data.status) {
                        showPresetMessage('تم التحديث بنجاح.');
                    } else {
                        showPresetMessage(data.message || 'فشل التحديث', true);
                    }
                })
                .catch(() => { setSaveButtonLoading(btn, false); showPresetMessage('خطأ في الاتصال', true); });
            } else {
                const name = nameInput.value.trim();
                fetch('{{ route("admin.multi-select-table.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ name, values })
                })
                .then(r => r.json())
                .then(data => {
                    setSaveButtonLoading(btn, false);
                    if (data.status) {
                        showPresetMessage('تم الحفظ بنجاح.');
                        window.location.href = '{{ route("admin.lens-power-presets.index") }}';
                    } else {
                        showPresetMessage(data.message || 'فشل الحفظ', true);
                    }
                })
                .catch(() => { setSaveButtonLoading(btn, false); showPresetMessage('خطأ في الاتصال', true); });
            }
        });

        if (window.initialPreset && window.initialPreset.id) {
            const preset = window.initialPreset;
            clearAll();
            const values = Array.isArray(preset.values) ? preset.values : [];
            values.forEach(item => {
                const sph = parseFloat(item.sph);
                const cyl = parseFloat(item.cyl);
                if (!Number.isNaN(sph) && !Number.isNaN(cyl)) {
                    const cell = findCellBySphCyl(sph, cyl);
                    if (cell) selectCell(cell);
                }
            });
            lastSelectedCell = null;
            refreshUI();
        }
    });
</script>
