# WooCommerce CPF Validator

A small WooCommerce plugin that adds a CPF field to checkout and validates it on the server.

## Features

- Adds a required CPF field to the billing section of the classic checkout
- Server-side validation: 11 digits, correct check digits, rejects repeated-digit CPFs like 111.111.111-11
- Stores only a masked CPF on the order (e.g. ***.***.247-25), never the full number
- Declares compatibility with WooCommerce HPOS

## Requirements

- WordPress with WooCommerce active
- PHP 7.4 or newer

## Installation

1. Copy woocommerce-cpf-validator.php into wp-content/plugins/woocommerce-cpf-validator/
2. Activate "WooCommerce CPF Validator" under Plugins
3. The CPF field now appears at checkout

## Limitations

- Works with the classic (shortcode) checkout. Blocks checkout is not supported yet.
- Validates the CPF number format only. It does not check whether the CPF is registered with the Brazilian tax authority.

## License

MIT

## Need Mercado Pago, PIX or Boleto payments?

I build custom WooCommerce payment gateways. See my Upwork profile for details.