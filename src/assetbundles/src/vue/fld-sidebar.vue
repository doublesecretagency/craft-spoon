<template>
    <div class="fld-sidebar">
        <div class="fld-field-library">
            <fld-search></fld-search>
            <fld-available></fld-available>
        </div>
    </div>
</template>

<script>
import FieldLayoutDesignerSearch from './fld-search';
import FieldLayoutDesignerAvailable from './fld-available';

export default {
    name: 'FieldLayoutDesignerSidebar',
    data() {
        return {};
    },
    components: {
        'fld-search': FieldLayoutDesignerSearch,
        'fld-available': FieldLayoutDesignerAvailable,
    }
}
</script>
