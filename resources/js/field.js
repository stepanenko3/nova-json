import JsonArrayIndexField from './components/Fields/JsonArray/IndexField'
import JsonArrayDetailField from './components/Fields/JsonArray/DetailField'
import JsonArrayFormField from './components/Fields/JsonArray/FormField'

import JsonRepeatableIndexField from './components/Fields/JsonRepeatable/IndexField'
import JsonRepeatableDetailField from './components/Fields/JsonRepeatable/DetailField'
import JsonRepeatableFormField from './components/Fields/JsonRepeatable/FormField'

Nova.booting((app, store) => {
    app.component('index-nova-json-array-field', JsonArrayIndexField)
    app.component('detail-nova-json-array-field', JsonArrayDetailField)
    app.component('form-nova-json-array-field', JsonArrayFormField)

    app.component('index-nova-json-repeatable-field', JsonRepeatableIndexField)
    app.component('detail-nova-json-repeatable-field', JsonRepeatableDetailField)
    app.component('form-nova-json-repeatable-field', JsonRepeatableFormField)
})
