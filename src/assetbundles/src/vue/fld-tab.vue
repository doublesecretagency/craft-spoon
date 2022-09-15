<template>
    <div class="fld-tab">
        <fld-options
            :index="index"
            v-if="fldStore.activeOptions === index"
        ></fld-options>
        <div class="tabs">
            <div class="tab sel draggable handle">
                <span>{{ name }}</span>
<!--                <a class="settings icon" title="{{ 'Edit'|t('app') }}"></a>-->
                <a
                    title="Edit"
                    class="settings icon"
                    @click="fldStore.openOptions(index)"
                ></a>
            </div>
        </div>
        <div class="fld-tabcontent" :data-name="name">
            <draggable
                :list="selected"
                group="elements"
                item-key="id"
                ghost-class="insertion"
                @start="pickUpElement"
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
    </div>
</template>

<script>
// Import Pinia
import { mapStores } from 'pinia';
import { useFldStore } from './stores/FldStore.js';

import FieldLayoutDesignerElement from './fld-element';
import FieldLayoutDesignerOptions from './fld-options';

import draggable from 'vuedraggable';

export default {
    name: 'FieldLayoutDesignerTab',
    components: {
        draggable,
        'fld-element': FieldLayoutDesignerElement,
        'fld-options': FieldLayoutDesignerOptions,
    },
    props: {
        name: String,
        index: Number,
        selected: Array
    },
    computed: {
        // Load Pinia store
        ...mapStores(useFldStore)
    },
    methods: {
        pickUpElement(evt) {
            console.log('picking up element');
            // console.log('picking up element', evt);
        }
    }
}
</script>
