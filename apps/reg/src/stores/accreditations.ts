import type { Accreditation, AccreditationOverviewLine } from '../../../common/api/schemas/accreditation';
import type { Ref } from 'vue';
import { ref } from 'vue'
import { defineStore } from 'pinia'
import { overview } from '../../../common/api/accreditations/overview';
import type { BadgeOverview } from './lib/accreditationtypes';
import { parseAccreditationOverview } from './lib/parseAccreditationOverview';
import { regenerate as regenerateAPI } from '../../../common/api/accreditations/regenerate';

export const useAccreditationsStore = defineStore('accreditations', () => {
    const accreditationData:Ref<BadgeOverview> = ref({countries:[], events:[], roles:[], templates:[]});

    function getAccreditationData()
    {
        return overview().then((data) => {
            if (data && data.length) {
                accreditationData.value = parseAccreditationOverview(data);
            }
        })
        .catch((e) => {
            console.log(e);
            alert('There was an error retrieving the accreditation data from the server. Please reload the page and try again.');
        });
    }

    function regenerate()
    {
        return regenerateAPI();
    }

    return {
        accreditationData,
        getAccreditationData,
        regenerate
    }
});
