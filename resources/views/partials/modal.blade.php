<div class="modal fade" id="sale-agent-mapping-modal" tabindex="-1" role="dialog" aria-labelledby="smallmodalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="smallmodalLabel">Add User Sales Agent Mapping</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form">
                    <div class="form-group">
                        <label for="sale-agent-mapping-company" class="control-label">Company*</label>
                        <select id="sale-agent-mapping-company" class="form-control"></select>
                        <span id="sale-agent-mapping-company-error-msg" class="error-msg">You must select a company.</span>
                    </div>
                    <div class="form-group">
                        <label for="sale-agent-mapping-agent" class="control-label">Sales Agent*</label>
                        <select id="sale-agent-mapping-agent" class="form-control"></select>
                        <span id="sale-agent-mapping-agent-error-msg" class="error-msg">You must select an agent.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="sale-agent-mapping-confirm-button" class="btn btn-primary">Add</button>
            </div>
        </div>
    </div>
</div>
