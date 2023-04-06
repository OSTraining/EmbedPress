<?php

namespace EmbedPress\Includes\Classes;
use \Elementor\Controls_Manager;

class Extend_Elementor_Controls {

	public function __construct () {
		add_action( 'extend_elementor_controls', [$this, 'extend_elementor_share_and_lock_controls'], 10, 4 );
	}
	
	public function extend_elementor_share_and_lock_controls($that, $infix = '', $pro_text = '', $pro_class = ''){
		$that->start_controls_section(
            'embedpress_content_protection_settings',
            [
                'label' => esc_html__('Content Protection', 'embedpress'),
            ]
        );

        $that->add_control(
			'embedpress'.$infix.'lock_content',
			[
				'label'        => sprintf(__('Enable Content Protection %s', 'embedpress'), $pro_text),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'return_value' => 'yes',
				'default'      => '',
				'classes'     => $pro_class,
			]
		);

		$that->add_control(
			'embedpress'.$infix.'lock_content_password',
			[
				'label'       => __('Set Password', 'embedpress'),
				'type'        => Controls_Manager::TEXT,
				'default'	=> '12345',
				'label_block' => false,
				'condition'   => [
					'embedpress'.$infix.'lock_content' => 'yes'
				]
			]
		);

		
		$that->add_control(
			'embedpress'.$infix.'lock_content_error_message',
			[
				'label' => __('Error Message', 'embedpress'),
				'type' => Controls_Manager::TEXT,
				'default' => 'Oops, that wasn\'t the right password. Try again.',
				'label_block' => false,
				'condition' => [
					'embedpress'.$infix.'lock_content' => 'yes'
				]
			]
		);
		$that->add_control(
			'embedpress'.$infix.'password_placeholder',
			[
				'label' => __('Placeholder', 'embedpress'),
				'type' => Controls_Manager::TEXT,
				'default' => 'Oops, that wasn\'t the right password. Try again.',
				'label_block' => false,
				'condition' => [
					'embedpress'.$infix.'lock_content' => 'yes'
				]
			]
		);
		$that->add_control(
			'embedpress'.$infix.'submit_button_text',
			[
				'label' => __('Button Text', 'embedpress'),
				'type' => Controls_Manager::TEXT,
				'default' => 'Oops, that wasn\'t the right password. Try again.',
				'label_block' => false,
				'condition' => [
					'embedpress'.$infix.'lock_content' => 'yes'
				]
			]
		);

        $that->add_control(
			'embedpress'.$infix.'lock_content_heading',
			[
				'label' => __('Header', 'embedpress'),
				'type' => Controls_Manager::TEXT,
				'default' => 'Content Locked',
				'label_block' => false,
				'condition' => [
					'embedpress'.$infix.'lock_content' => 'yes'
				]
			]
		);
		
		$that->add_control(
			'embedpress'.$infix.'lock_content_sub_heading',
			[
				'label' => __('Description', 'embedpress'),
				'type' => Controls_Manager::TEXT,
				'default' => 'This content is locked and requires a password to access it.',
				'label_block' => false,
				'condition' => [
					'embedpress'.$infix.'lock_content' => 'yes'
				]
			]
		);
		

		$that->add_control(
			'embedpress'.$infix.'enable_footer_message',
			[
				'label'        => __('Footer Text', 'embedpress'),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'return_value' => 'yes',
				'default'      => '',
			]
		);
		  
		$that->add_control(
			'embedpress'.$infix.'lock_content_footer_message',
			[
				'label' => __('Footer', 'embedpress'),
				'type' => Controls_Manager::TEXT,
				'default' => 'In case you don\'t have the password, kindly reach out to content owner or administrator to request access.',
				'label_block' => false,
				'condition' => [
					'embedpress'.$infix.'enable_footer_message' => 'yes'
				]
			]
		);
        
        $that->end_controls_section();

        $that->start_controls_section(
            'embedpress_content_share_settings',
            [
                'label' => esc_html__('Content Share', 'embedpress'),
            ]
        );

        $that->add_control(
			'embedpress'.$infix.'content_share',
			[
				'label'        => __('Enable Content Share', 'embedpress'),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'return_value' => 'yes',
				'default'      => '',
			]
		);
        $that->add_control(
            'embedpress'.$infix.'content_share_position',
            [
                'label'   => __('Position', 'embedpress'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'right',
                'options' => [
                    'top'        => __('Top', 'embedpress'),
                    'right' => __('Right', 'embedpress'),
                    'bottom'    => __('Bottom', 'embedpress'),
                    'left'  => __('Left', 'embedpress'),
                ],
                'condition'   => [
					'embedpress'.$infix.'content_share' => 'yes'
				]
            ]
        );
        $that->add_control(
            'embedpress'.$infix.'content_title',
            [
                'label'   => __('Title', 'embedpress'),
                'type'    => Controls_Manager::TEXT,
                'placeholder' => 'Enter share title',
                'condition'   => [
					'embedpress'.$infix.'content_share' => 'yes'
				]
            ]
        );
        $that->add_control(
            'embedpress'.$infix.'content_descripiton',
            [
                'label'   => __('Description', 'embedpress'),
                'type'    => Controls_Manager::TEXTAREA,
                'placeholder' => 'Enter share description',
                'condition'   => [
					'embedpress'.$infix.'content_share' => 'yes'
				]
            ]
        );

        $that->add_control(
			'embedpress'.$infix.'content_share_custom_thumbnail',
			[
				'label' => esc_html__( 'Thumbnail', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
                'condition'   => [
					'embedpress'.$infix.'content_share' => 'yes'
				]
			]
		);
        $that->end_controls_section();

	}


}