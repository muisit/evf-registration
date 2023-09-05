import { ref } from 'vue'
import type { Ref } from 'vue';
import { defineStore } from 'pinia'

export const useDataStore = defineStore('data', () => {
    const categories = ref([]);
    const categoriesById = ref({});
    const roles = ref([]);
    const rolesById = ref({});
    const weapons = ref([]);
    const weaponsById = ref({});
    const countries = ref([]);
    const countriesById = ref({});

    return {
        categories, categoriesById,
        roles, rolesById,
        weapons, weaponsById,
        countries, countriesById
    }
})
