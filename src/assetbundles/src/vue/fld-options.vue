<template>
    <div class="fld-options">
        <ul role="listbox">
            <li><a @click="fldStore.tabRename(index)" role="option" tabindex="-1">Rename</a></li>
            <li><a @click="fldStore.tabRemove(index)" role="option" tabindex="-1">Remove</a></li>
        </ul>
        <hr>
        <ul role="listbox">
            <li><a @click="fldStore.tabMoveLeft(index)" role="option" tabindex="-1" :class="{'disabled': index === 0}">Move to the left</a></li>
            <li><a @click="fldStore.tabMoveRight(index)" role="option" tabindex="-1" :class="{'disabled': index === fldStore.maxIndex}">Move to the right</a></li>
        </ul>
    </div>
</template>

<script>
// Import Pinia
import { mapStores } from 'pinia';
import { useFldStore } from './stores/FldStore.js';

export default {
    name: 'FieldLayoutDesignerTabOptions',
    props: {
        index: Number,
    },
    computed: {
        // Load Pinia store
        ...mapStores(useFldStore)
    }
}
</script>
