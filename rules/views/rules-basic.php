<?php
$automation_id = BWFAN_Core()->automations->get_automation_id();
$groups        = [];

if ( empty( $groups ) ) {
	$default_rule_id = 'rule' . uniqid();
	$groups          = array(
		'group' . ( time() ) => array(
			$default_rule_id => array(
				'rule_type' => 'general_always',
				'operator'  => '==',
				'condition' => '',
			),
		),

	);
}
?>

<div class="bwfan-rules-builder woocommerce_options_panel">
    <div class="label">
        <h4><?php esc_html_e( 'Advanced Rules', 'wp-marketing-automations' ); ?></h4>
    </div>
    <div id="bwfan-rules-groups" class="bwfan_rules_common">
        <div class="bwfan-rule-group-target">
			<?php if ( is_array( $groups ) ) : ?>
			<?php
			$group_counter = 0;
			foreach ( $groups as $group_id => $group ) :
				if ( empty( $group_id ) ) {
					$group_id = 'group' . $group_id;
				}
				?>
                <div class="bwfan-rule-group-container" data-groupid="<?php esc_attr_e( $group_id ); ?>">
                    <div class="bwfan-rule-group-header">
						<?php if ( 0 === $group_counter ) : ?>
                            <h4><?php esc_html_e( 'Initiate this automation when these conditions are matched:', 'wp-marketing-automations' ); ?></h4>
						<?php endif; ?>
                    </div>
					<?php
					if ( is_array( $group ) ) :
						?>
                        <table class="bwfan-rules" data-groupid="<?php esc_attr_e( $group_id ); ?>">
                            <tbody>
							<?php
							foreach ( $group as $rule_id => $rule ) :
								if ( empty( $rule_id ) ) {
									$rule_id = 'rule' . $rule_id;
								}
								?>
                            <tr data-ruleid="<?php esc_attr_e( $rule_id ); ?>" class="bwfan-rule">
                                <td class="rule-type">
									<?php
									// allow custom location rules
									$types = apply_filters( 'bwfan_rule_get_rule_types', array() );

									// create field
									$args = array(
										'input'      => 'select',
										'name'       => 'bwfan_rule[' . $group_id . '][' . $rule_id . '][rule_type]',
										'class'      => 'rule_type',
										'choices'    => $types,
										'allow_null' => true,
										'null_text'  => __( 'Select a rule', '' ),
									);
									bwfan_Input_Builder::create_input_field( $args, $rule['rule_type'] );
									?>
                                </td>

                                <td class="condition"></td>
                                <td class="loading" colspan="2" style="display:none;"><?php esc_html_e( 'Loading...', 'wp-marketing-automations' ); ?></td>
                                <td class="add">
                                    <a href="#"
                                       class="bwfan-add-rule button"><?php esc_html_e( 'AND', 'wp-marketing-automations' ); ?></a>
                                </td>
                                <td class="remove">
                                    <a href="#" class="bwfan-remove-rule bwfan-button-remove"
                                       title="<?php esc_attr_e( 'Remove condition', 'wp-marketing-automations' ); ?>"></a>
                                </td>
                                </tr><?php endforeach; ?></tbody>
                        </table>
					<?php endif; ?>
                </div>
				<?php $group_counter ++; ?>
			<?php endforeach; ?>
        </div>

        <button class="button button-primary bwfan-add-rule-group" title="<?php esc_attr_e( 'Add a set of conditions', 'wp-marketing-automations' ); ?>"><?php esc_html_e( 'OR', 'wp-marketing-automations' ); ?></button>
		<?php endif; ?>
    </div>
</div>
