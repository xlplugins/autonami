<td class="remove">
    <span>IF</span>
    <a href="#" class="bwfan-remove-rule bwfan-button-remove">Delete</a>
</td>
<td class="rule-type">
	<?php
	$types = apply_filters( 'bwfan_rule_get_rule_types', array() );
	// create field
	$args = array(
		'input'      => 'select',
		'name'       => 'bwfan_rule[<%= groupId %>][<%= ruleId %>][rule_type]',
		'class'      => 'rule_type',
		'choices'    => $types,
		'allow_null' => true,
		'null_text'  => __( 'Select a rule', '' ),
	);

	?>

    <select id="" class="rule_type" name="bwfan_rule[<%= groupId %>][<%= ruleId %>][rule_type]">
        <option value="null"> Select a rule</option>

        <% _(ruleSet).each(function(k,v) { %>
        <optgroup label="<%= rulesGroups[v].title %>">
            <% _(k).each(function(k,v) { %>
            <option value="<%= v %>"><%= k %></option>
            <% }) %>
        </optgroup>
        <% }) %>

    </select>

</td>

<td class="loading" colspan="2" style="display:none;"><?php esc_html_e( 'Loading...', 'wp-marketing-automations' ); ?></td>
<td class="add"><a href="#" class="bwfan-add-rule button"><?php esc_html_e( 'AND', 'wp-marketing-automations' ); ?></a></td>
