require('./support/helpers')
window.$ = window.jQuery = require('jquery')
require('./bootstrap')

require('./apps/search.js')

require('./apps/addresses.js')
require('./apps/contacts.js')
require('./apps/personal-info.js')
require('./apps/contact-outside-workflow.js')
require('./apps/progresses.js')
require('./apps/committees.js')
require('./apps/committee-services.js')
require('./apps/records.js')
require('./apps/committees-search.js')
require('./apps/committees-search.js')
require('./apps/phones.js')
require('./apps/chat.js')
require('./apps/home.js')
require('./apps/advanced-search.js')
require('./apps/areas-search.js')
require('./apps/areas.js')
require('./apps/origins-search.js')
require('./apps/origins.js')

require('./components/select2.js')
