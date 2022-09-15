<template>
    <div class="texticon search icon clearable">
        <input
            type="text"
            class="fullwidth text"
            inputmode="search"
            autocomplete="off"
            placeholder="Search"
            dir="ltr"
            @keyup="fldStore.setSearchFilter($event)"
        >
<!--        placeholder="{{ "Search"|t('app') }}"-->

<!--    <div class="clear hidden" title="{{ 'Clear'|t('app') }}"></div>-->
        <div class="clear hidden" title="Clear"></div>
    </div>
</template>

<script>
// Import Pinia
import { mapStores } from 'pinia';
import { useFldStore } from './stores/FldStore.js';

export default {
    name: 'FieldLayoutDesignerSearch',
    data() {
        return {};
    },
    computed: {
        // Load Pinia store
        ...mapStores(useFldStore)
    }
}
</script>
