<script type="text/html" id="tmpl-recipe-list-template">
    <#

    #>
    <div class="bwfan_r_single">
        <div class="bwfan_r_s_cont">
            <div class="bwfan_r_s_name">{{data.details.name}}</div>
            <div class="bwfan_r_s_desc">{{data.details.description}}</div>
        </div>
        <div class="bwfan_r_s_action">
            <a class="button button_secondary" href="javascript:void(0)" data-slug="{{data.slug}}" data-izimodal-open="#modal-show-recipe-import">Import</a>
        </div>
    </div>
</script>
