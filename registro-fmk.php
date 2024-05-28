<?php

/**
 * Plugin Name: Registro Woocommerce a medida 
 * Plugin URI:  https://jjmontalban.github.io
 * Description: Tiny plugin to add register fields. And extra tax number field. CIF or VAT with SOAP validation service 
 * Author:      JJMontalban
 * Author URI:  https://jjmontalban.github.io
 * Version:     0.1.0
 */



//Includes
use CheckVat\checkVat as Vat;
use CheckVat\checkVatService;

require __DIR__ . '/vendor/autoload.php';


//Campos del checkout de woocommerce
function custom_override_checkout_fields( $fields ) 
{
    unset($fields['billing']['billing_last_name']);
	unset($fields['shipping']['shipping_last_name']);
    //cambia nombre del label de los campos nombre y empresa
    $fields['billing']['billing_first_name']['label'] = 'Nombre (Persona de contacto)';
    $fields['billing']['billing_company']['label'] = 'Empresa (Razón Social)';
    $fields['shipping']['shipping_first_name']['label'] = 'Nombre (Persona de contacto)';
    //hacer que sea obligatorio
    $fields['billing']['billing_company']['required'] = true;
    
    return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );


//Campos de la página de direccion de facturacion
function custom_remove_last_name_field_from_my_account( $fields ) {
    $fields['billing_first_name']['label'] = 'Nombre (Persona de contacto)';
    $fields['billing_company']['required'] = true;
    unset( $fields['billing_last_name'] );
    return $fields;
}
add_filter( 'woocommerce_billing_fields', 'custom_remove_last_name_field_from_my_account', 5 );


//Campos de la página de direccion de envio
function shipping_remove_fields( $fields )
{
    $fields['shipping_first_name']['label'] = 'Nombre (Persona de contacto)';
    unset( $fields[ 'shipping_last_name' ] );
    return $fields; 
}
add_filter( 'woocommerce_shipping_fields', 'shipping_remove_fields' );


//Elimina obligatoriedad del campo apellido y de nombre visible
function custom_override_account_fields($fields) {
    unset( $fields[ 'account_display_name' ] );
    unset( $fields[ 'account_last_name' ] );
    return $fields;
}   
add_filter('woocommerce_save_account_details_required_fields', 'custom_override_account_fields');


// Añadir CSS después de ejecutar el anterior filtro y ocultar campos
function custom_add_css_after_override_account_fields() {
    ?>
    <style>
        label[for="account_last_name"], #account_last_name,
        label[for="account_display_name"], #account_display_name,
        #account_display_name + span{
            display:none;
        }
        .woocommerce-MyAccount-content .form-row-first {
            width: 100%;
        }
        #billing_first_name_field,
        #shipping_first_name_field
        {
            width: 100%;
        }
    </style>
    <?php
}
add_action( 'wp_head', 'custom_add_css_after_override_account_fields' );


// Añadir un nuevo campo CIF
function custom_modify_billing_fields( $fields ) {
    // Añadir nuevo campo CIF
    $fields['billing_cif'] = array(
        'label'       => __('CIF', 'woocommerce'),
        'placeholder' => _x('', 'placeholder', 'woocommerce'),
        'required'    => true,
        'clear'       => false,
        'type'        => 'text',
        'class'       => array('input-text'),
        'description' => 'Identificación Fiscal',
    );

    return $fields;
}
add_filter( 'woocommerce_billing_fields', 'custom_modify_billing_fields' );


//Añadir nuevo campo a la ficha de cliente
function custom_woocommerce_customer_meta_fields( $fields ) 
{
    $fields['billing']['fields']['billing_cif'] = array( 'label' => __( 'CIF', 'woocommerce' ), 'description' => 'Puede ser CIF español, portugués o Intracomunitario PT');
		
    return $fields;
}
add_filter( 'woocommerce_customer_meta_fields', 'custom_woocommerce_customer_meta_fields' );


//1. Añadir campos al registro
//   https://www.cloudways.com/blog/add-woocommerce-registration-form-fields/
function wooc_extra_register_fields() {?>
    <p class="form-row form-row-first">
        <label for="reg_billing_first_name"><?php echo('Nombre (Persona de contacto)'); ?><span class="required">*</span></label>
        <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" 
                value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" />
    </p>
    <p class="form-row form-row-last">
        <label for="reg_billing_company"><?php _e( 'Empresa (Razón Social)', 'woocommerce' ); ?><span class="required">*</span></label>
        <input type="text" class="input-text" name="billing_company" id="reg_billing_company" 
                value="<?php if ( ! empty( $_POST['billing_company'] ) ) esc_attr_e( $_POST['billing_company'] ); ?>" />
    </p>
    <p class="form-row form-row-wide">
        <label for="reg_billing_phone"><?php _e( 'Phone', 'woocommerce' ); ?><span class="required">*</span></label>
        <input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php esc_attr_e( $_POST['billing_phone'] ); ?>" />
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
        // Encolar scripts necesarios
        wp_enqueue_script('wc-country-select');
        wp_enqueue_script('wc-state-select');

        // Campo de país
        woocommerce_form_field('billing_country', array(
            'type'          => 'country', // Tipo de campo correcto
            'class'         => array('form-row-wide', 'address-field', 'update_totals_on_change'),
            'label'         => __('País', 'woocommerce'),
            'placeholder'   => __('Escoge tu país.', 'woocommerce'),
            'required'      => true,
            'clear'         => true,
        ));

        // Campo de estado
        woocommerce_form_field('billing_state', array(
            'type'          => 'state', // Tipo de campo correcto
            'class'         => array('form-row-wide', 'address-field'),
            'label'         => __('Provincia', 'woocommerce'),
            'placeholder'   => __('Escoge tu provincia.', 'woocommerce'),
            'required'      => true,
            'clear'         => true,
        ));
        ?>
        <!-- Extra field CIF -->
        <p class="form-row form-row-wide">
            <label for="billing_cif"><?php _e( 'NIF ( VAT si tiene nº iva europeo ) ', 'woocommerce' ); ?></label>
            <input type="text" class="input-text" name="billing_cif" id="billing_cif" value="<?php if ( ! empty( $_POST['billing_cif'] ) ) esc_attr_e( $_POST['billing_cif'] ); ?>" />
        </p>

        <div class="clear"></div>
        <!-- campo de recargo -->
        <p class="form-row form-row-wide">
            <label for="cliente_recargo">
                <input type="checkbox" name="cliente_recargo" id="cliente_recargo" value="clientesconre" />
                <?php _e( 'Tengo recargo de equivalencia ', 'woocommerce' ); ?>
            </label>
        </p>
        <?php
}
add_action( 'woocommerce_register_form_start', 'wooc_extra_register_fields' );


//2. Validacion de los campos
function wooc_validate_extra_register_fields( $username, $email, $validation_errors ) 
{
     // Verificar si el checkbox de aceptación de política de privacidad está marcado
     if ( ! isset( $_POST['politica_privacidad_registro'] ) || empty( $_POST['politica_privacidad_registro'] ) ) {
        $validation_errors->add( 'politica_privacidad_registro_error', __( 'Debe aceptar la política de privacidad para registrarse.', 'woocommerce' ) );
    }

    if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
            $validation_errors->add( 'billing_first_name_error', __( 'Nombre es requerido', 'woocommerce' ) );
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

            //Check with VIES
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

            //get company name from VIES
            $_POST['billing_company'] = $result['name'];
    }
    else{
            $validation_errors->add( 'billing_cif_error', __( 'El NIF/VAT introducido debe tener 9 u 11 dígitos.', 'woocommerce' ) );
    }
	

    return $validation_errors;
}

add_action( 'woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3 );


// 3. Gestionar clientes con recargo

// 3.1. Crear el perfil de usuario clientesconre
add_role( 'clientesconre', __('Clientes con recargo' ),array('read' => true, ));

//3.2 Asignar el rol en la creacion de la cuenta de cliente
function fmk_save_extra_recargo_register_checkbox_field( $customer_id ) 
{
    if ( isset($_POST['cliente_recargo']) && $_POST['cliente_recargo'] == 'clientesconre' ) 
    {
        $user = new WP_User($customer_id);
        $user->set_role('clientesconre');
    }
}
add_action( 'woocommerce_created_customer', 'fmk_save_extra_recargo_register_checkbox_field' );

// 3.3 Aplicar recargo de equivalencia a productos (y envio) del carrito de clientes con recargo
function wooc_recargo_equivalencia( $tax_class, $product ) {  
    if ( is_user_logged_in() && current_user_can( 'clientesconre' ) ) {
        if ( 'tasa-estandar' === $tax_class ) {
            return 'tasa-estandar-re';
        } 
        if ( 'tasa-reducida' === $tax_class ) {
            return 'tasa-reducida-re';
        }
    } 
    return $tax_class;  
}
add_filter( 'woocommerce_product_get_tax_class', 'wooc_recargo_equivalencia', 1, 2 );

//4. Insertar datos en la database
function wooc_save_extra_register_fields( $customer_id ) {

    if ( isset( $_POST['billing_phone'] ) ) {
        update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
    }

    if ( isset( $_POST['billing_first_name'] ) ) {
        update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
        update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
    }

    if ( isset( $_POST['billing_company'] ) ) {
        update_user_meta( $customer_id, 'billing_company', sanitize_text_field( $_POST['billing_company'] ) );
    }

    if ( isset( $_POST['billing_cif'] ) ) {
        update_user_meta( $customer_id, 'billing_cif', sanitize_text_field( $_POST['billing_cif'] ) );
    }

    if ( isset( $_POST['billing_address_1'] ) ) {
        update_user_meta( $customer_id, 'billing_address_1', sanitize_text_field( $_POST['billing_address_1'] ) );
    }

    if ( isset( $_POST['billing_city'] ) ) {
        update_user_meta( $customer_id, 'billing_city', sanitize_text_field( $_POST['billing_city'] ) );
    }

    if ( isset( $_POST['billing_postcode'] ) ) {
        update_user_meta( $customer_id, 'billing_postcode', sanitize_text_field( $_POST['billing_postcode'] ) );
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


/* Añadir checkbox de aceptacion de politica de privacidad */
add_action( 'woocommerce_register_form', function () {

    woocommerce_form_field( 'politica_privacidad_registro', array(
        'type'          => 'checkbox',
        'class'         => array('form-row rgpd'),
        'label_class'   => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
        'input_class'   => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
        'required'      => true,
        'label'         => 'He leído y acepto la política de privacidad',
        )); 
}, 30);

//Agrega el campo cif a los emails de pedido
function custom_add_cif_to_order_emails( $fields, $sent_to_admin, $order ) {
    $billing_cif = $order->get_meta( '_billing_cif', true );
    
    if ( ! empty( $billing_cif ) ) {
        $fields['_billing_cif'] = array(
            'label' => __( 'CIF', 'woocommerce' ),
            'value' => $billing_cif,
        );
    }
    
    return $fields;
}
add_filter( 'woocommerce_email_order_meta_fields', 'custom_add_cif_to_order_emails', 10, 3 );


/***************************** 
 * 
 * Caso Portugal con VAT
 * 
*/


//Rol de cliente especial exento de impuestos
add_role( 'portugal', __('portugal' ),array( 'read' => true ));

// Los clientes portugueses con VAT se les asigna el rol portugal
function wc_save_registration_form_fields( $customer_id ) {
    if ( isset($_POST['role']) ) {
        if( $_POST['role'] == 'portugal' ){
            $user = new WP_User($customer_id);
            $user->set_role('portugal');
        }
    }
}
add_action( 'woocommerce_created_customer', 'wc_save_registration_form_fields' );

//Override WooCommerce tax display option for portugueses.
//@see http://stackoverflow.com/questions/29649963/displaying-taxes-in-woocommerce-by-user-role
function tax_category_role( $tax_class ) 
{
    if ( current_user_can( 'portugues' ) ) {
        $tax_class = 'Tasa Cero';
    }
    
    return $tax_class;
}

add_filter( 'woocommerce_before_cart_contents', 'tax_category_role', 1, 2 );
add_filter( 'woocommerce_before_shipping_calculator', 'tax_category_role', 1, 2);
add_filter( 'woocommerce_before_checkout_billing_form', 'tax_category_role', 1, 2 );
add_filter( 'woocommerce_product_get_tax_class', 'tax_category_role', 1, 2 );
add_filter( 'woocommerce_product_variation_get_tax_class', 'tax_category_role', 1, 2 );
