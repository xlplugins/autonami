<script type="text/template" id="tmpl-bwfan-task-popup">

    <div class="bwfan-task_pop_cont"><?php esc_html_e( 'Details', 'wp-marketing-automations' ); ?></div>

    <ul>
        <li>
            <div class="bwfan-task_vl"><?php esc_html_e( 'Automation:', 'wp-marketing-automations' ); ?></div>
            <div class="bwfan-task_vr">
                <# if ( data.details.status == 0) { #>
                {{data.details.automation_name}}
                <# } else { #>
                <a href="{{data.details.automation_url}}">{{data.details.automation_name}}</a>
                <# } #>
            </div>
        </li>
        <li>
            <div class="bwfan-task_vl"><?php esc_html_e( 'Event:', 'wp-marketing-automations' ); ?></div>
            <div class="bwfan-task_vr">{{data.details.automation_source}}: {{data.details.automation_event}}</div>
        </li>
        <li>
            <div class="bwfan-task_vl"><?php esc_html_e( 'Action:', 'wp-marketing-automations' ); ?></div>
            <div class="bwfan-task_vr">{{data.details.task_integration}}: {{data.details.task_integration_action}}</div>
        </li>
        <li>
            <div class="bwfan_task_details"></div>
        </li>
    </ul>

    <div class="bwfan_clear_10"></div>

    <# if( _.size(data.task_message) > 0 ){ #>
    <div class="bwfan-task_pop_cont"><?php esc_html_e( 'Notes', 'wp-marketing-automations' ); ?></div>
    <div class="bwfan_clear_20"></div>

    <div class="bwfan-task_vr">
        <#
        i = 0;
        _.each( data.task_message, function( value, key ){
        #>
        <div class="bwfan-task_notes">
            <# if( 0 == (i%2) ){ #>
            <div class="bwfan-task_notes_card bwfan_orange_notes_card">{{value}}</div>
            <# }else{ #>
            <div class="bwfan-task_notes_card bwfan_blue_notes_card">{{value}}</div>
            <# } #>
            <div class="bwfan-task_notes_time"><i>{{key}}</i></div>
        </div>
        <#
        i++;
        })
        #>
    </div>

    <# } #>

</script>
<div class="bwfan_izimodal_default" style="display: none" id="modal-show-task-details">
    <div class="sections bwfan-bg-white">
        <div id="bwfan-task-section">

        </div>
    </div>
</div>
