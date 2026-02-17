<style>
    /* ========== Sticky Save Bar ========== */
    .sticky-save-bar {
        position: sticky;
        bottom: 0;
        z-index: 100;
        background: #fff;
        border-top: 2px solid #e4e6ef;
        padding: 12px 24px;
        box-shadow: 0 -4px 12px rgba(0,0,0,0.06);
        margin-top: 10px;
    }

    /* ========== Table Base ========== */
    .lens-table {
        border-collapse: separate;
        border-spacing: 0;
        font-size: 11px;
        font-family: 'Courier New', monospace;
        width: auto;
    }

    /* ========== Corner Cell ========== */
    .corner-cell {
        position: sticky;
        top: 0;
        left: 0;
        z-index: 30;
        background: #2b2d42;
        color: #fff;
        width: 60px;
        min-width: 60px;
        height: 36px;
        padding: 0;
        border: 1px solid #1a1b2e;
    }

    .corner-label {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .corner-sph {
        position: absolute;
        bottom: 2px;
        left: 4px;
        font-size: 9px;
        opacity: 0.7;
    }

    .corner-cyl {
        position: absolute;
        top: 2px;
        right: 4px;
        font-size: 9px;
        opacity: 0.7;
    }

    .corner-line {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
    }

    .corner-line::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: linear-gradient(to top right, transparent calc(50% - 0.5px), rgba(255,255,255,0.3), transparent calc(50% + 0.5px));
    }

    /* ========== Column Headers (CYL) ========== */
    .col-header {
        position: sticky;
        top: 0;
        z-index: 20;
        background: #2b2d42;
        color: #edf2f4;
        text-align: center;
        padding: 6px 2px;
        min-width: 50px;
        max-width: 50px;
        font-weight: 600;
        font-size: 10px;
        border: 1px solid #1a1b2e;
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
        transition: background 0.15s;
    }

    .col-header:hover {
        background: #3a3d5c !important;
    }

    /* ========== Row Headers (SPH) ========== */
    .row-header {
        position: sticky;
        left: 0;
        z-index: 15;
        background: #2b2d42;
        color: #edf2f4;
        text-align: center;
        padding: 4px 6px;
        min-width: 60px;
        max-width: 60px;
        font-weight: 600;
        font-size: 10px;
        border: 1px solid #1a1b2e;
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
        transition: background 0.15s;
    }

    .row-header:hover {
        background: #3a3d5c !important;
    }

    /* ========== Cells ========== */
    .selectable-cell {
        width: 50px;
        min-width: 50px;
        max-width: 50px;
        height: 28px;
        text-align: center;
        padding: 0;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        cursor: cell;
        user-select: none;
        transition: background 0.1s, box-shadow 0.1s;
        position: relative;
    }

    .selectable-cell:hover {
        background: #dbeafe !important;
        box-shadow: inset 0 0 0 2px #93c5fd;
        z-index: 5;
    }

    /* ========== Zero Line ========== */
    .zero-row {
        border-top: 2px solid #f59e0b !important;
        border-bottom: 2px solid #f59e0b !important;
        background: #fffde7 !important;
    }

    .zero-col {
        border-left: 2px solid #f59e0b !important;
        border-right: 2px solid #f59e0b !important;
        background: #fffde7 !important;
    }

    .zero-cell {
        background: #fef3c7 !important;
        border: 2px solid #f59e0b !important;
    }

    /* ========== Selected State ========== */
    .cell-selected {
        background: #3b82f6 !important;
        border-color: #2563eb !important;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.3);
        z-index: 5;
    }

    .cell-selected::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        opacity: 0.9;
    }

    .cell-selected:hover {
        background: #2563eb !important;
    }

    /* ========== Header highlight when row/col fully selected ========== */
    .header-all-selected {
        background: #3b82f6 !important;
        color: white !important;
    }

    /* ========== Crosshair: hover highlights ========== */
    .col-header.header-hover {
        background: #4a6fa5 !important;
        color: #fff !important;
        box-shadow: inset 0 -3px 0 0 #93c5fd;
    }

    .row-header.header-hover {
        background: #4a6fa5 !important;
        color: #fff !important;
        box-shadow: inset -3px 0 0 0 #93c5fd;
    }

    .selectable-cell.cell-col-hover {
        background: #eef4ff;
    }

    .selectable-cell.cell-row-hover {
        background: #eef4ff;
    }

    .selectable-cell.cell-col-hover.cell-row-hover {
        background: #dbeafe;
    }

    .selectable-cell.cell-selected.cell-col-hover,
    .selectable-cell.cell-selected.cell-row-hover {
        background: #3b82f6 !important;
    }

    /* ========== Scrollbar ========== */
    #tableWrapper::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    #tableWrapper::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    #tableWrapper::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 4px;
    }

    #tableWrapper::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }

    /* ========== Formula Panel ========== */
    .collapse-icon {
        transition: transform 0.3s;
    }

    [aria-expanded="false"] .collapse-icon {
        transform: rotate(-90deg);
    }

    #formulaPanel .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
    }

    #selectedInfo .values-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }

    #selectedInfo .value-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        font-size: 11px;
        font-family: 'Courier New', monospace;
        white-space: nowrap;
    }

    /* Preset Name Validation */
    #presetName.is-invalid { border-color: #f1416c; box-shadow: 0 0 0 0.2rem rgba(241,65,108,0.15); }
</style>
