
---

# **bKash Payment Driver for Laravel**

[![Latest Version on Packagist](https://img.shields.io/packagist/v/durrbar/payment-bkash-driver.svg?style=flat-square)](https://packagist.org/packages/durrbar/payment-bkash-driver)
[![Total Downloads](https://img.shields.io/packagist/dt/durrbar/payment-bkash-driver.svg?style=flat-square)](https://packagist.org/packages/durrbar/payment-bkash-driver)

A Laravel package to integrate the **bKash Payment Gateway** seamlessly into your application. This package supports tokenized payments, refunds, transaction verification, and handling callbacks (IPN, success, failure, cancel). It is designed to work with the `durrbar/payment-module` for shared payment driver functionality.

---

## **Features**
- **Tokenized Payments**: Supports secure tokenized payment flows.
- **Refunds**: Initiate and check the status of refunds.
- **Transaction Verification**: Verify payment transactions using transaction IDs.
- **Callback Handling**: Handle IPN, success, failure, and cancellation callbacks.
- **Sandbox Support**: Easily switch between sandbox and live environments for testing.
- **Queue Integration**: Automatically retry refund status checks using queued jobs.

---

## **Requirements**
- PHP >= 8.0
- Laravel >= 9.0
- `durrbar/payment-module` (for shared payment driver functionality)

---

## **Installation**

Install the package via Composer:

```bash
composer require durrbar/payment-bkash-driver
```

---

## **Configuration**

Add the following variables to your `.env` file:

```env
BKASH_SANDBOX=true
BKASH_APP_KEY=your_app_key
BKASH_APP_SECRET=your_app_secret
BKASH_USERNAME=your_username
BKASH_PASSWORD=your_password
BKASH_CALLBACK_URL=https://your-domain.com/bkash/callback
```

The configuration will automatically load from `payment.providers.bkash` in your Laravel application.

---

## **Usage**

This package is designed to work seamlessly with the `PaymentService` from the `durrbar/payment-module`. All payment-related operations are handled automatically by the `PaymentService`. Simply configure the package and specify `bkash` as the provider when interacting with the `PaymentService`.

### **How It Works**
1. Install the package and configure the `.env` file with your bKash credentials.
2. The `PaymentService` dynamically resolves this package as the driver for bKash payments.
3. All payment-related operations (initiating payments, handling callbacks, refunds, etc.) are handled automatically by the `PaymentService`.

No additional setup or manual integration is required beyond installing the package and adding the configuration.

---

### **Supported Operations**
The following operations are supported and handled automatically by the `PaymentService`:
- **Initiating a Payment**: Payments are initiated using the bKash API.
- **Handling Callbacks**: IPN, success, failure, and cancellation callbacks are processed automatically.
- **Verifying a Payment**: Payment transactions are verified using transaction IDs.
- **Refunding a Payment**: Refunds can be initiated and their status checked automatically.
- **Checking Refund Status**: The `PaymentService` checks the status of refunds using queued jobs.

---

## **Queue Jobs**
The package includes a queued job (`CheckBkashRefundStatusJob`) to automatically check the status of refunds after 30 seconds. Ensure your queue worker is running:

```bash
php artisan queue:work
```

---

## **Testing**
To test the package in sandbox mode, set `BKASH_SANDBOX=true` in your `.env` file. Use the sandbox credentials provided by bKash.

---

## **Contributing**
Contributions are welcome! Please follow these steps:
1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Submit a pull request with a detailed description of your changes.

---

## **Security**
If you discover any security-related issues, please email the maintainer instead of using the issue tracker.

---

## **Credits**
- [Your Name](https://github.com/officialkidmax)
- Inspired by [bKash API Documentation](https://developer.bka.sh/)

---

## **License**
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---
