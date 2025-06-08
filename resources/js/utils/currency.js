const currencySettings = {
    vi: { currency: 'VND', locale: 'vi-VN', symbolPosition: 'after' },
    en: { currency: 'VND', locale: 'en-US', symbolPosition: 'after' },
    ja: { currency: 'VND', locale: 'ja-JP', symbolPosition: 'after' },
    ko: { currency: 'VND', locale: 'ko-KR', symbolPosition: 'after' },
    zh: { currency: 'VND', locale: 'zh-CN', symbolPosition: 'after' }
}

export function formatCurrency(value, currentLocale = 'vi') {
    const config = currencySettings[currentLocale] || currencySettings.vi
    const { currency, locale, symbolPosition } = config

    if (isNaN(value)) return ''

    const formatter = new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    })

    let formatted = formatter.format(value)

    if (currency === 'VND' && symbolPosition === 'after') {
        formatted = formatted.replace(/[₫\s]/g, '').trim() + ' ₫'
    }

    return formatted
}