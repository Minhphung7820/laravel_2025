import { createI18n } from 'vue-i18n'
import vi from './../lang/vi.json'
import en from './../lang/en.json'
import ja from './../lang/ja.json'
import ko from './../lang/ko.json'

const locale = localStorage.getItem('lang') || 'vi'

const i18n = createI18n({
    legacy: false,
    globalInjection: true,
    locale,
    fallbackLocale: 'vi',
    messages: { vi, en, ja, ko }
})

export default i18n