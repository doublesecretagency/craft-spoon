<template>
    <div style="width:100%">
        <input type="hidden" name="config" :value="JSON.stringify(fldStore.config)">
        <fld-workspace></fld-workspace>
        <fld-sidebar></fld-sidebar>
    </div>
</template>

<script>
// Import Pinia
import { mapStores } from 'pinia';
import { useFldStore } from './stores/FldStore.js';

import FieldLayoutDesignerWorkspace from './fld-workspace';
import FieldLayoutDesignerSidebar from './fld-sidebar';

export default {
    name: 'FieldLayoutDesigner',
    components: {
        'fld-workspace': FieldLayoutDesignerWorkspace,
        'fld-sidebar': FieldLayoutDesignerSidebar,
    },
    props: {
        label: String,
        config: Array,
        elements: Array,
    },
    data() {
        return {};
    },
    computed: {
        // Load Pinia store
        ...mapStores(useFldStore)
    },
    setup(props) {
        // Get the Pinia store
        const fldStore = useFldStore();
        // Set Pinia values from props
        fldStore.label = props.label;
        fldStore.config = props.config;
        fldStore.elements = props.elements;
    },
}
</script>
