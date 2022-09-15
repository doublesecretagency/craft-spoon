<template>
    <div class="fld-field-group">
        <h6>{{ fldStore.label }}</h6>
        <draggable
            :list="fldStore.unselected"
            group="elements"
            item-key="id"
            ghost-class="insertion"
            @start="grabUnselected"
        >
            <template #item="{element, index}">
                <div>
                    <fld-element
                        :id="element"
                        :element="fldStore.elementDetails(element)"
                        :key="element"
                    ></fld-element>
                </div>
            </template>
        </draggable>
    </div>
</template>

<script>
// Import Pinia
import { mapStores } from 'pinia';
import { useFldStore } from './stores/FldStore.js';

import FieldLayoutDesignerElement from './fld-element';

import draggable from 'vuedraggable';

export default {
    name: 'FieldLayoutDesignerAvailable',
    components: {
        draggable,
        'fld-element': FieldLayoutDesignerElement,
    },
    computed: {
        // Load Pinia store
        ...mapStores(useFldStore)
    },
    methods: {
        grabUnselected(evt) {
            console.log('grabbing an unselected element');
            // console.log('grabbing an unselected element', evt);
        },
    }
}
</script>
