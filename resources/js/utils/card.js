/**
 * Client-side helpers for card-number input fields. Card numbers and CVVs
 * validated with these never need to leave the browser — callers should
 * only ever submit a masked last4/brand/expiry to the server.
 */

export function luhnValid(digits) {
    let sum = 0;
    let alternate = false;

    for (let i = digits.length - 1; i >= 0; i -= 1) {
        let n = parseInt(digits[i], 10);
        if (alternate) { n *= 2; if (n > 9) n -= 9; }
        sum += n;
        alternate = !alternate;
    }

    return digits.length >= 12 && sum % 10 === 0;
}

export function expiryValid(value) {
    const match = /^(\d{2})\/(\d{2})$/.exec(value);
    if (!match) return false;

    const month = Number(match[1]);
    const year = 2000 + Number(match[2]);
    if (month < 1 || month > 12) return false;

    return new Date(year, month, 1) > new Date();
}

export function detectCardBrand(digits) {
    if (/^4/.test(digits)) return 'Visa';
    if (/^(5[1-5]|2[2-7])/.test(digits)) return 'Mastercard';
    if (/^3[47]/.test(digits)) return 'Amex';
    if (/^6(011|5)/.test(digits)) return 'Discover';
    return 'Card';
}

export function formatCardNumber(rawValue) {
    const digits = rawValue.replace(/\D/g, '').slice(0, 19);
    return digits.replace(/(.{4})/g, '$1 ').trim();
}

export function formatExpiry(rawValue) {
    const digits = rawValue.replace(/\D/g, '').slice(0, 4);
    return digits.length > 2 ? `${digits.slice(0, 2)}/${digits.slice(2)}` : digits;
}
