import { ref } from 'vue'
import { defineStore } from 'pinia'
import { basicData } from '../../../common/api/basicdata';

export const useDataStore = defineStore('data', () => {
    const categories = ref([]);
    const categoriesById = ref({});
    const roles = ref([]);
    const rolesById = ref({});
    const weapons = ref([]);
    const weaponsById = ref({});
    const countries = ref([]);
    const countriesById = ref({});

    function hasBasicData() {
        return this.categories.length > 0;
    }

    function getBasicData() {
        if (!this.hasBasicData()) {
            this.categories = [];
            this.categoriesById = {};
            this.roles = [];
            this.rolesById = {};
            this.weapons = [];
            this.weaponsById = {};
            this.countries = [];
            this.countriesById = {};

            const self = this;
            basicData()
                .then((data) => {
                    self.fillData(data);
                })
                .catch((e) => {
                    console.log(e);
                    //setTimeout(() => { self.getBasicData(); }, 500);
                });
        }
    }

    function fillData(data:object) {
        if (data.categories) {
            this.categories = data.categories;
            this.categories.forEach((item) => {
                this.categoriesById['c' + item.id] = item;
            });
        }

        if (data.weapons) {
            this.weapons = data.weapons;
            this.weapons.forEach((item) => {
                this.weaponsById['w' + item.id] = item;
            });
        }

        if (data.countries) {
            this.countries = data.countries;
            this.countries.forEach((item) => {
                this.countriesById['c' + item.id] = item;
            });
        }

        if (data.roles) {
            this.roles = data.roles;
            this.roles.forEach((item) => {
                this.rolesById['r' + item.id] = item;
            });
        }
    }

    return {
        categories, categoriesById,
        roles, rolesById,
        weapons, weaponsById,
        countries, countriesById,
        fillData, getBasicData, hasBasicData,
    }
})
