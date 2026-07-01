import { usePage } from '@inertiajs/vue3';

/**
 * Formats a number as a currency string using the business setting currency symbol.
 */
export function formatMoney(value: number | string): string {
    const page = usePage();
    const branding = page.props.branding as any;
    const settings = branding?.business_settings || {};
    const symbol = settings.currency_symbol || '$';

    const amount = typeof value === 'string' ? parseFloat(value) : value;

    if (isNaN(amount)) return `${symbol}0.00`;

    const formattedAmount = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);

    return `${symbol}${formattedAmount}`;
}

export const money = formatMoney;
