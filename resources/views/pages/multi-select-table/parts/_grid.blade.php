{{-- Axis Labels --}}
<div class="d-flex mb-2">
    <div style="width: 60px;"></div>
    <div class="text-center flex-grow-1">
        <span class="badge bg-info px-4 py-2 fs-7">CYL (اسطوانة)</span>
    </div>
</div>

<div class="d-flex">
    {{-- SPH Label --}}
    <div class="d-flex align-items-center justify-content-center" style="width: 28px; min-width: 28px;">
        <span class="badge bg-success py-2 px-3 fs-7" style="writing-mode: vertical-rl; transform: rotate(180deg); white-space: nowrap;">SPH (كروية)</span>
    </div>

    {{-- Table --}}
    <div class="table-responsive flex-grow-1" id="tableWrapper" style="max-height: 70vh; overflow: auto;">
        <table id="lensPowerTable" class="lens-table">
            <thead>
                <tr>
                    <th class="corner-cell">
                        <div class="corner-label">
                            <span class="corner-sph">SPH</span>
                            <span class="corner-line"></span>
                            <span class="corner-cyl">CYL</span>
                        </div>
                    </th>
                    @foreach($cylValues as $index => $cyl)
                    <th class="col-header selectable-col-header" data-col="{{ $index }}" title="اضغط لتحديد العمود">
                        {{ $cyl >= 0 ? '+' : '' }}{{ number_format($cyl, 2) }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($sphValues as $sphIndex => $sph)
                <tr>
                    <th class="row-header selectable-row-header" data-row="{{ $sphIndex }}" title="اضغط لتحديد الصف">
                        {{ $sph >= 0 ? '+' : '' }}{{ number_format($sph, 2) }}
                    </th>
                    @foreach($cylValues as $cylIndex => $cyl)
                    <td class="selectable-cell {{ $sph == 0 && $cyl == 0 ? 'zero-cell' : '' }} {{ $sph == 0 ? 'zero-row' : '' }} {{ $cyl == 0 ? 'zero-col' : '' }}"
                        data-sph="{{ $sph }}"
                        data-cyl="{{ $cyl }}"
                        data-row="{{ $sphIndex }}"
                        data-col="{{ $cylIndex }}">
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Legend --}}
<div class="d-flex align-items-center gap-4 mt-3 px-2">
    <span class="text-muted fs-8"><i class="fas fa-square text-primary me-1"></i> محدد</span>
    <span class="text-muted fs-8"><span class="d-inline-block border border-2 border-warning me-1" style="width:12px; height:12px; background:#fffde7;"></span> خط الصفر</span>
    <span class="text-muted fs-8"><span class="d-inline-block border me-1" style="width:12px; height:12px; background:#f0f7ff;"></span> غير محدد</span>
</div>
