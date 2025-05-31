import puppeteer from 'puppeteer';
const connectionURL = 'wss://browser.zenrows.com?apikey=ee9ee7c3543c173d8ceadadcbcd047d09c888283&proxy_region=na';

const firstName = process.argv[2];
const lastName = process.argv[3];
const email = process.argv[4];
const phone = process.argv[5];
const phonecode = process.argv[6];
const cardNumber = process.argv[7];
const cardMonth = process.argv[8];
const cardYear = process.argv[9];
const securityCode = process.argv[10];

const getCsrfToken = `document.querySelector('meta[name="csrf-token"]').getAttribute('content')`;
const getCartToken = `getCookie('cart_token')`;
const fetchAbandoned = `(async () => {
  const response = await fetch("https://tuplace.mycartpanda.com/abandoned", {
    "headers": {
      "accept": "*/*",
      "accept-language": "pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7,zh-CN;q=0.6,zh;q=0.5",
      "checkout-country": "US",
      "checkout-currency": "USD",
      "content-type": "application/x-www-form-urlencoded; charset=UTF-8",
      "priority": "u=1, i",
      "sec-fetch-dest": "empty",
      "sec-fetch-mode": "cors",
      "sec-fetch-site": "same-origin",
      "x-csrf-token": "{{csrftoken}}",
      "x-requested-with": "XMLHttpRequest"
    },
    "referrer": "https://tuplace.mycartpanda.com/checkout",
    "referrerPolicy": "strict-origin-when-cross-origin",
    "body": "customer%5Bemail%5D={{email}}&customer%5Bstate_reg_num%5D=&customer%5BphoneNumber%5D={{phone}}&customer%5Bphonecode%5D=%2B{{phonecode}}&customer%5BfirstName%5D={{firstname}}&customer%5BlastName%5D={{lastname}}&customer%5Bcpn%5D=&customer%5Bcountry%5D=&cartToken={{cartToken}}&country_code=",
    "method": "POST",
    "mode": "cors",
    "credentials": "include"
  });
  return await response.json();
})()`;

const fetchGatewayPayment = `(async () => {
  const response = await fetch("https://tuplace.mycartpanda.com/gatewaypay", {
    "headers": {
      "accept": "*/*",
      "accept-language": "pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7,zh-CN;q=0.6,zh;q=0.5",
      "content-type": "application/json",
      "priority": "u=1, i",
      "sec-fetch-dest": "empty",
      "sec-fetch-mode": "cors",
      "sec-fetch-site": "cross-site"
    },
    "referrer": "https://tuplace.mycartpanda.com/",
    "referrerPolicy": "strict-origin-when-cross-origin",
    "body": JSON.stringify({
      "current_route": "checkout",
      "cartpay_checkout": "0",
      "cartpay_enabled": "0",
      "cart_country_code": "US",
      "is_global_market": "1",
      "checkout_request_currency": "USD",
      "cartTotalWeight": "15000",
      "checkoutSubTotalPrice": "6",
      "checkoutTotalPrice": "6",
      "checkoutTotalPriceGlobal": "6",
      "totalShippingPrice": "0",
      "totalShippingPriceGlobal": "0",
      "totalTax": "0",
      "totalDiscount": "0.00",
      "include_shipping_amount": "",
      "discount_category": "",
      "discountCode": "0",
      "giftDiscountPrice": "0",
      "giftDiscountCode": "0",
      "shipping_gateway": "0",
      "melhor_envio_service": "0",
      "melhor_envio_company": "0",
      "melhor_envio_packages": "0",
      "paid_by_client": "0",
      "custom_price": "0",
      "digital_cart_items": "1",
      "country_code": "",
      "ocu_exists": "0",
      "browser_ip": document.querySelector('input[name="browser_ip"]').getAttribute('value'),
      "quantity": "1",
      "couponCode": "",
      "email": "{{email}}",
      "fullName": "{{fullname}}",
      "phoneNumber": "{{phone}}",
      "phonecode": "{{phonecode}}",
      "ficalNumber": "",
      "cnpjNumber": "",
      "registrationNumber": "",
      "zipcode": "",
      "city": "",
      "state": "",
      "address": "",
      "number": "",
      "neighborhood": "",
      "compartment": "",
      "country": "",
      "cardNumber": "{{cardnumber}}",
      "cardholderName": "{{fullname}}",
      "cardExpiryDate": "{{cardmonth}}/{{cardyear}}",
      "securityCode": "{{securitycode}}",
      "installments": "1",
      "save_information": "1",
      "ebanking": "Pix",
      "docType": "",
      "docNumber": "",
      "site_id": "MLB",
      "cardExpirationMonth": "{{cardmonth}}",
      "cardExpirationYear": "{{cardyear}}",
      "paymentMethodId": "cc",
      "recover_source": "",
      "alert_message_product_qty_not_available": "Não há estoque para os produtos que você está tentando comprar.",
      "alert_message_cart_is_empty": "Ops! Parece que seu carrinho está vazio.",
      "sayswho": "a",
      "addCCDiscountPrice": "0",
      "paymentMethod": "cc",
      "payment_type": "cartpanda_stripe",
      "payment_token": "upnid",
      "visitorID": "lalf",
      "cart_token": "{{carttoken}}",
      "abandoned_token": "null",
      "currency": "USD"
    }),
    "method": "POST",
  });
  return await response.json();
})()`;

(async () => {
    const browser = await puppeteer.connect({ browserWSEndpoint: connectionURL });
    const page = await browser.newPage();
    await page.setRequestInterception(true);
    page.on('request', (req) => {
        if (['image', 'stylesheet', 'font'].includes(req.resourceType())) {
            req.abort();
        } else {
            req.continue();
        }
    });
    await page.goto('https://tuplace.mycartpanda.com/checkout/183511281:1');
    await page.waitForSelector('meta[name="csrf-token"]');
    const csrfToken = await page.evaluate(getCsrfToken);
    const cartToken = await page.evaluate(getCartToken);

    const replacedFetchAbandoned = fetchAbandoned.replace('{{csrftoken}}', csrfToken).replace('{{cartToken}}', cartToken).replace('{{email}}', email).replace('{{phone}}', phone).replace('{{phonecode}}', phonecode).replace('{{firstname}}', firstName).replace('{{lastname}}', lastName);

    const abandoned = await page.evaluate(replacedFetchAbandoned);

    const replacedFetchGatewayPayment = fetchGatewayPayment.replaceAll('{{email}}', email).replaceAll('{{fullname}}', firstName + ' ' + lastName).replaceAll('{{phone}}', phone).replaceAll('{{phonecode}}', phonecode).replaceAll('{{cardnumber}}', cardNumber).replaceAll('{{cardmonth}}', cardMonth).replaceAll('{{cardyear}}', cardYear).replaceAll('{{securitycode}}', securityCode).replaceAll('{{carttoken}}', cartToken);

    const gatewayPayment = await page.evaluate(replacedFetchGatewayPayment);

    console.log(gatewayPayment);
    await browser.close();
})();