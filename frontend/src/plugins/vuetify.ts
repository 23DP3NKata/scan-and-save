import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'

import { createVuetify } from 'vuetify'
import { lv } from 'vuetify/locale'

export default createVuetify({
  locale: { locale: 'lv', fallback: 'lv', messages: { lv } },
  date: { locale: { lv: 'lv-LV' } },
  icons: {
    defaultSet: 'mdi',
  },
})
