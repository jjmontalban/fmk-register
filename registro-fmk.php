<?php

/**
 * Plugin Name: Registro Woocommerce a medida 
 * Plugin URI:  https://jjmontalban.github.io
 * Description: Tiny plugin to add register fields. And extra tax number field. CIF or VAT with SOAP validation service 
 * Author:      JJMontalban
 * Author URI:  https://jjmontalban.github.io
 * Version:     0.1.0
 */

use CheckVat\checkVat as Vat;
use CheckVat\checkVatService;

require __DIR__ . '/vendor/autoload.php';

/**
 * New fild in billing form
*/

function custom_woocommerce_billing_fields($fields)
{
    $fields['billing_cif'] = array(
        'label' => __('CIF', 'woocommerce'), // Add custom field label
        'placeholder' => _x('', 'placeholder', 'woocommerce'), // Add custom field placeholder
        'required' => true,
        'clear' => false, // add clear or not
        'type' => 'text', // add field type
        'class' => array('input-text'),
        'description' => 'Identificacion Fiscal',
    );

    return $fields;
}

add_filter('woocommerce_billing_fields', 'custom_woocommerce_billing_fields');




/**
 * Añadir campos a la ficha de cliente
 */
function custom_woocommerce_customer_meta_fields( $fields ) 
{
    $fields['billing']['fields']['billing_cif'] = array( 'label' => __( 'CIF', 'woocommerce' ), 'description' => 'Puede ser CIF español, portugués o Intracomunitario PT');
		
    return $fields;
}
add_filter( 'woocommerce_customer_meta_fields', 'custom_woocommerce_customer_meta_fields' );




/**
 * @snippet       Añadir campos al registro
 *                https://www.cloudways.com/blog/add-woocommerce-registration-form-fields/
 */

// Add new register fields for WooCommerce registration.
function wooc_extra_register_fields() {?>
       <p class="form-row form-row-first">
	       <label for="reg_billing_first_name"><?php echo('Nombre'); ?><span class="required">*</span></label>
		   <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" 
				  value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" />
       </p>
	   <p class="form-row form-row-first">
	       <label for="reg_billing_last_name"><?php echo('Apellidos'); ?><span class="required">*</span></label>
		   <input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" 
				  value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) esc_attr_e( $_POST['billing_last_name'] ); ?>" />
       </p>
       <p class="form-row form-row-last">
	       <label for="reg_billing_company"><?php _e( 'Empresa', 'woocommerce' ); ?></label>
	       <input type="text" class="input-text" name="billing_company" id="reg_billing_company" 
                  value="<?php if ( ! empty( $_POST['billing_company'] ) ) esc_attr_e( $_POST['billing_company'] ); ?>" />
       </p>
       <p class="form-row form-row-wide">
           <label for="reg_billing_phone"><?php _e( 'Phone', 'woocommerce' ); ?><span class="required">*</span></label>
	       <input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" 
                  value="<?php esc_attr_e( $_POST['billing_phone'] ); ?>" />
        </p>
        <p class="form-row form-row-wide">
            <label for="reg_billing_address_1"><?php _e( 'Address', 'woocommerce' ); ?><span class="required">*</span></label>
            <input type="text" class="input-text" name="billing_address_1" id="reg_billing_address_1" 
                   value="<?php esc_attr_e( $_POST['billing_address_1'] ); ?>" />
        </p>
        <p class="form-row form-row-wide">
            <label for="reg_billing_city"><?php _e( 'City', 'woocommerce' ); ?><span class="required">*</span></label>
            <input type="text" class="input-text" name="billing_city" id="reg_billing_city" 
                   value="<?php esc_attr_e( $_POST['billing_city'] ); ?>" />
        </p>
        <p class="form-row form-row-wide">
            <label for="reg_billing_postcode"><?php _e( 'Postal Code', 'woocommerce' ); ?><span class="required">*</span></label>
            <input type="text" class="input-text" name="billing_postcode" id="reg_billing_postcode" 
                   value="<?php esc_attr_e( $_POST['billing_postcode'] ); ?>" />
        </p>
       <div class="clear"></div>
       <?php

       //Dropdowns especiales
       wp_enqueue_script( 'wc-country-select' );

	   woocommerce_form_field( 'billing_country', array(
	        'type'      => 'country',
	        'class'     => array('chzn-drop'),
	        'label'     => __('Country'),
	        'placeholder' => __('Escoge tu país.'),
	        'required'  => true,
	        'clear'     => true
	    ));


	   wp_enqueue_script( 'wc-state-select' );

	   woocommerce_form_field( 'billing_state', array(
	        'type'      => 'state',
	        'class'     => array('chzn-drop'),
	        'label'     => __('Provincia'),
	        'placeholder' => __('Escoge tu provincia.'),
	        'required'  => true,
	        'clear'     => true
	    ));

        ?>
       
        <!-- Extra field CIF -->
        <p class="form-row form-row-last">
            <label for="reg_billing_cif"><?php _e( 'NIF ( VAT si tiene nº iva europeo ) ', 'woocommerce' ); ?></label>
            <input type="text" class="input-text" name="billing_cif" id="reg_billing_cif" value="<?php if ( ! empty( $_POST['billing_cif'] ) ) esc_attr_e( $_POST['billing_cif'] ); ?>" />
        </p>
  		<?php
 }

 add_action( 'woocommerce_register_form_start', 'wooc_extra_register_fields' );












//2. Validacion de los campos
function wooc_validate_extra_register_fields( $username, $email, $validation_errors ) {

      if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
             $validation_errors->add( 'billing_first_name_error', __( 'Nombre es requerido', 'woocommerce' ) );
      }
	
	  if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {
             $validation_errors->add( 'billing_last_name_error', __( 'Apellidos es requerido', 'woocommerce' ) );
      }

      if ( isset( $_POST['billing_phone'] ) && empty( $_POST['billing_phone'] ) ) {
             $validation_errors->add( 'billing_phone_error', __( 'Telefono es requerido', 'woocommerce' ) );
      }

      if ( isset( $_POST['billing_address_1'] ) && empty( $_POST['billing_address_1'] ) ) {
             $validation_errors->add( 'billing_address_1_error', __( 'Dirección es requerido', 'woocommerce' ) );
      }

      if ( isset( $_POST['billing_city'] ) && empty( $_POST['billing_city'] ) ) {
             $validation_errors->add( 'billing_city_error', __( 'Ciudad es requerido', 'woocommerce' ) );
      }

      if ( isset( $_POST['billing_postcode'] ) && empty( $_POST['billing_postcode'] ) ) {
             $validation_errors->add( 'billing_postcode_error', __( 'Código postal es requerido', 'woocommerce' ) );
      }
	  
	  if ( isset( $_POST['billing_country'] ) && empty( $_POST['billing_country'] ) ) {
             $validation_errors->add( 'billing_country_error', __( 'País es requerido', 'woocommerce' ) );
      }
	
	  //CIF Case
	  if ( isset( $_POST['billing_cif'] ) && strlen( $_POST['billing_cif'] ) == 9 )
      {     
            //Already registered?
            $hasCif = get_users('meta_value='.$_POST['billing_cif']);
            if ( !empty( $hasCif ) ) {
                $validation_errors->add( 'billing_cif_error', __( 'El NIF introducido ya ha sido registrado.', 'woocommerce' ) );
            }      
	  }

      //VAT Case (only Portugal)
      else if( isset( $_POST['billing_cif'] ) && strlen( $_POST['billing_cif'] ) == 11 )
      {
            //Already registered?
            $hasVat = get_users('meta_value='.$_POST['billing_cif']);
            if ( !empty( $hasVat ) ) {
                $validation_errors->add( 'billing_cif_error', __( 'El VAT introducido ya ha sido registrado.', 'woocommerce' ) );
            }
            //Check VAT with VIES
            $vat_number = $_POST['billing_cif'];   
            $country_code = substr($vat_number, 0, 2);
            $output      = substr($vat_number, 2);
            $number      = preg_replace('/[^0-9]/', '', $output);

            $service = new checkVatService();
            $param = new Vat;
            $param->countryCode = $country_code;
            $param->vatNumber = $number;
            $result = json_decode( json_encode( $service->checkVat( $param ) ), true );

            if( !$result['valid'] ){
                $validation_errors->add( 'billing_cif_error', __( 'El VAT introducido no existe.', 'woocommerce' ) );
            }
            $_POST['billing_company'] = $result['name'];
      }
      else{
            $validation_errors->add( 'billing_cif_error', __( 'El NIF/VAT introducido debe tener 9 u 11 dígitos.', 'woocommerce' ) );
      }
	

     return $validation_errors;
}

add_action( 'woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3 );









//3. Insertar datos en la database
function wooc_save_extra_register_fields( $customer_id ) {

      if ( isset( $_POST['billing_phone'] ) ) {
                 // Phone input field which is used in WooCommerce
                 update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( strtoupper( $_POST['billing_phone'] ) ) );
      }

	  if ( isset( $_POST['billing_first_name'] ) ) {
	         //First name field which is by default
	         update_user_meta( $customer_id, 'first_name', sanitize_text_field(  strtoupper( $_POST['billing_first_name'] ) ) );
	         // First name field which is used in WooCommerce
	         update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field(  strtoupper( $_POST['billing_first_name'] ) ) );
	  }
	
	  if ( isset( $_POST['billing_last_name'] ) ) {
	         //Last name field which is by default
	         update_user_meta( $customer_id, 'last_name', sanitize_text_field(  strtoupper( $_POST['billing_last_name'] ) ) );
	         // Last name field which is used in WooCommerce
	         update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field(  strtoupper( $_POST['billing_last_name'] ) ) );
	  }

	  if ( isset( $_POST['billing_company'] ) ) {
                 update_user_meta( $customer_id, 'billing_company', sanitize_text_field(  strtoupper( $_POST['billing_company'] ) ) );
      }

      if ( isset( $_POST['billing_cif'] ) ) {
                 update_user_meta( $customer_id, 'billing_cif', sanitize_text_field(  strtoupper( $_POST['billing_cif'] ) ) );
      }

      if ( isset( $_POST['billing_address_1'] ) ) {
                 update_user_meta( $customer_id, 'billing_address_1', sanitize_text_field(  strtoupper( $_POST['billing_address_1'] ) ) );
      }

      if ( isset( $_POST['billing_city'] ) ) {
                 update_user_meta( $customer_id, 'billing_city', sanitize_text_field(  strtoupper( $_POST['billing_city'] ) ) );
      }

      if ( isset( $_POST['billing_postcode'] ) ) {
                 update_user_meta( $customer_id, 'billing_postcode', sanitize_text_field(  strtoupper( $_POST['billing_postcode'] ) ) );
      }

      if ( isset( $_POST['billing_country'] ) ) {
                 update_user_meta( $customer_id, 'billing_country', sanitize_text_field(  strtoupper( $_POST['billing_country'] ) ) );
      }

      if ( isset( $_POST['billing_state'] ) ) {
                 update_user_meta( $customer_id, 'billing_state', sanitize_text_field(  strtoupper( $_POST['billing_state'] ) ) );
      }
	
	  if ( isset( $_POST['billing_email'] ) ) {
                 update_user_meta( $customer_id, 'billing_email', sanitize_text_field(  strtoupper( $_POST['billing_email'] ) ) );
      }

}

add_action( 'woocommerce_created_customer', 'wooc_save_extra_register_fields' );


