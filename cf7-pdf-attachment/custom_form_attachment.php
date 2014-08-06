<?php
/*
 Plugin Name: Ovp Custom CF7 Form Attachment
Plugin URI: http://developers.overplace.com/moduli
Description: Plugin per generare un pdf e allegarlo alle mail prodotte da cf7
Version: 1.0
Author: Overplace Developer
Author URI: http://developers.overplace.com
License: GPL2
*/

if(in_array( 'contact-form-7/wp-contact-form-7.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {


	if(!class_exists('custom_cf7_att')){
		
		class custom_cf7_att {
			
			/**
			 * @var WPCF7_ContactForm
			 */
			private $form;
			
			private $template;
			
			private $properties;
			
			public function __construct(){
				add_action('wpcf7_before_send_mail', array($this, 'check_form'),10, 1);
			}
			
			public function check_form(WPCF7_ContactForm $form){
				$this->form = $form;
				$this->properties = $form->get_properties();
				
				if(!empty($this->properties['additional_settings']) && $this->properties['additional_settings'] == 'pdf_attachment' && !empty($this->properties['mail']['body'])){
					require_once 'html2pdf/html2pdf.class.php';
					
					$args = array(
							'html' => $html,
							'exclude_blank' => $this->template['exclude_blank'] 
						);
					$this->template = '<html><body>'.wpautop(wpcf7_mail_replace_tags($this->properties['mail']['body'], $args)).'</body></html>';
					$this->genera_pdf();
				}
			}
			
			public function genera_pdf(){
				
				$permessi = 0775;
				$upload_dir = wp_upload_dir();
				$path_allegati_preventivi_app = $upload_dir['basedir'].'/cf7_pdf_attachment/';
				
				
				if(!is_dir($path_allegati_preventivi_app)){
					@mkdir($path_allegati_preventivi_app,$permessi,true);
					@chmod($path_allegati_preventivi_app,$permessi);
				}
				
				$fname = uniqid('richiesta_preventivo_').'.pdf';
				
				$html2pdf = new HTML2PDF('P', 'A4', 'it');
				$html2pdf->writeHTML($this->template, false);
				$html2pdf->Output($path_allegati_preventivi_app.$fname, 'F');
				
				if(file_exists($path_allegati_preventivi_app.$fname)){
					$this->properties['mail']['attachments'] = $upload_dir['basedir'].'/cf7_pdf_attachment/'.$fname;
					$this->form->set_properties($this->properties);
				}
			}
			
		}
	}
	new custom_cf7_att();
}