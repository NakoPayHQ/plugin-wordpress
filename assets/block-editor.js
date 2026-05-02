( function( wp ) {
    var el = wp.element.createElement;
    var registerBlockType = wp.blocks.registerBlockType;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var TextControl = wp.components.TextControl;
    var SelectControl = wp.components.SelectControl;

    registerBlockType( 'nakopay/pay-button', {
        title: 'NakoPay Pay Button',
        description: 'Add a Bitcoin / crypto pay button.',
        icon: 'money-alt',
        category: 'widgets',
        attributes: {
            amount: { type: 'string', default: '25' },
            currency: { type: 'string', default: 'USD' },
            coin: { type: 'string', default: 'BTC' },
            description: { type: 'string', default: '' },
            label: { type: 'string', default: 'Pay with Bitcoin' },
            style: { type: 'string', default: 'default' },
        },
        edit: function( props ) {
            var attrs = props.attributes;
            var set = props.setAttributes;

            var previewClass = 'nakopay-preview';
            if ( attrs.style === 'outline' ) previewClass += ' nakopay-preview--outline';
            if ( attrs.style === 'minimal' ) previewClass += ' nakopay-preview--minimal';

            return el(
                'div',
                { className: props.className },
                el( InspectorControls, null,
                    el( PanelBody, { title: 'Payment Settings', initialOpen: true },
                        el( TextControl, {
                            label: 'Amount',
                            value: attrs.amount,
                            onChange: function( v ) { set( { amount: v } ); },
                        }),
                        el( SelectControl, {
                            label: 'Currency',
                            value: attrs.currency,
                            options: [
                                { label: 'USD', value: 'USD' },
                                { label: 'EUR', value: 'EUR' },
                                { label: 'GBP', value: 'GBP' },
                                { label: 'CAD', value: 'CAD' },
                                { label: 'AUD', value: 'AUD' },
                                { label: 'JPY', value: 'JPY' },
                            ],
                            onChange: function( v ) { set( { currency: v } ); },
                        }),
                        el( SelectControl, {
                            label: 'Cryptocurrency',
                            value: attrs.coin,
                            options: [
                                { label: 'Bitcoin (BTC)', value: 'BTC' },
                                { label: 'Litecoin (LTC)', value: 'LTC' },
                                { label: 'Monero (XMR)', value: 'XMR' },
                            ],
                            onChange: function( v ) { set( { coin: v } ); },
                        }),
                        el( TextControl, {
                            label: 'Description',
                            value: attrs.description,
                            onChange: function( v ) { set( { description: v } ); },
                        }),
                        el( TextControl, {
                            label: 'Button Label',
                            value: attrs.label,
                            onChange: function( v ) { set( { label: v } ); },
                        }),
                        el( SelectControl, {
                            label: 'Style',
                            value: attrs.style,
                            options: [
                                { label: 'Default (filled)', value: 'default' },
                                { label: 'Outline', value: 'outline' },
                                { label: 'Minimal (text)', value: 'minimal' },
                            ],
                            onChange: function( v ) { set( { style: v } ); },
                        })
                    )
                ),
                el( 'div', { className: 'wp-block-nakopay-pay-button' },
                    el( 'span', { className: previewClass },
                        attrs.label + ' - ' + attrs.amount + ' ' + attrs.currency
                    )
                )
            );
        },
        save: function() {
            // Server-side rendered
            return null;
        },
    });
})( window.wp );
