<template>
    <div class="fld-workspace" style="height:100%">
        <div class="fld-tabs">

            <draggable
                :list="fldStore.config"
                group="tabs"
                item-key="index"
                handle=".handle"
                ghost-class="insertion"
            >
                <template #item="{element, index}">
                    <fld-tab
                        :index="index"
                        :name="element.name"
                        :selected="element.ids"
                    ></fld-tab>
                </template>
            </draggable>

        </div>
<!--        <div class="fld-new-tab-btn btn add icon">{{ "New Tab"|t('app') }}</div>-->
        <div class="fld-new-tab-btn btn add icon" @click="fldStore.addTab()">New Tab</div>
    </div>
</template>

<script>
// Import Pinia
import { mapStores } from 'pinia';
import { useFldStore } from './stores/FldStore.js';

import FieldLayoutDesignerTab from './fld-tab';

import draggable from 'vuedraggable';

export default {
    name: 'FieldLayoutDesignerWorkspace',
    components: {
        draggable,
        'fld-tab': FieldLayoutDesignerTab,
    },
    computed: {
        // Load Pinia store
        ...mapStores(useFldStore)
    }
}
</script>
