<?php

namespace App\Enums;

enum PaymentTypeEnum: string
{
    case CreditCard = 'credit_card';
    case CashOnDelivery = 'cash_on_delivery';
    case BankTransfer = 'bank_transfer';
}
