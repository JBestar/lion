<main class="el-main">
    <div class="roundstat-toolbar">
        <div class="roundstat-toolbar-left">
            <span class="roundstat-round-line">블록관리 (회차별통계 잠금)</span>
        </div>
        <button type="button" class="el-button el-button--mini el-button--primary" id="blockmanage-refresh-btn"><span>새로고침</span></button>
    </div>

    <div class="el-divider el-divider--horizontal"></div>

    <div class="el-table el-table--fit el-table--small el-table--enable-row-hover roundstat-table-shell">
        <div class="el-table__body-wrapper is-scrolling-none">
            <table class="el-table__body roundstat-table blockmanage-table" cellspacing="0" cellpadding="0" border="0">
                <thead>
                    <tr>
                        <th><div class="cell">구분</div></th>
                        <th><div class="cell">블록키</div></th>
                        <th><div class="cell">누적실패</div></th>
                        <th><div class="cell">차단시각</div></th>
                        <th><div class="cell">최근갱신</div></th>
                        <th><div class="cell">관리</div></th>
                    </tr>
                </thead>
                <tbody id="blockmanage-tbody-id"></tbody>
            </table>
        </div>
    </div>
</main>

<script src="<?php echo base_url('assets/js/admin/blockmanage-adm.js'); ?>"></script>
