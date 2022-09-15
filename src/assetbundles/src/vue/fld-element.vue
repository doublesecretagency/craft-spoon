<template>
    <div class="fld-field fld-element">
        <div class="field-name">
            <h4 class="fld-element-label" :title="element.name">{{ element.name }}</h4>
            <div class="smalltext light code" :title="element.handle">{{ element.handle }}</div>
        </div>
        <a
            title="Edit"
            class="settings icon edit"
            role="button"
            tabindex="0"
            @click="fldStore.openBlockFieldLayout(element)"
        ></a>
    </div>
</template>

<script>
// Import Pinia
import { mapStores } from 'pinia';
import { useFldStore } from './stores/FldStore.js';

export default {
    name: 'FieldLayoutDesignerElement',
    props: {
        id: Number,
        element: Object,
    },
    computed: {
        // Load Pinia store
        ...mapStores(useFldStore)
    }
}
</script>
