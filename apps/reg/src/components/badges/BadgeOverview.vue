<script lang="ts" setup>
import { computed } from 'vue';
import type { Ref } from 'vue';
import type { CountPerCountry, CountPerEvent, CountPerRole, CountPerTemplate } from '../../stores/lib/accreditationtypes';
import { useAccreditationsStore } from '../../stores/accreditations';

const accreditations = useAccreditationsStore();

const sortedCountries:Ref<Array<CountPerCountry>> = computed(() => {
    return accreditations.accreditationData.countries.sort((lna:CountPerCountry, lnb:CountPerCountry) => {
        if (!lna.country && !lnb.country) return 0;
        if (!lna.country) return 1;
        if (!lnb.country) return -1;
        return lna.country.name > lnb.country.name ? 1 : -1;
    });
});

const sortedEvents:Ref<Array<CountPerEvent>> = computed(() => {
    return accreditations.accreditationData.events.sort((lna:CountPerEvent, lnb:CountPerEvent) => {
        if (!lna.sideEvent && !lnb.sideEvent) return 0;
        if (!lna.sideEvent) return 1;
        if (!lnb.sideEvent) return -1;
        return lna.sideEvent.title > lnb.sideEvent.title ? 1 : -1;
    });
});

const sortedRoles:Ref<Array<CountPerRole>> = computed(() => {
    return accreditations.accreditationData.roles.sort((lna:CountPerRole, lnb:CountPerRole) => {
        if (!lna.role && !lnb.role) return 0;
        if (!lna.role) return 1;
        if (!lnb.role) return -1;
        if (lna.role.type != lnb.role.type) {
            if (lna.role.type == 'Country') return -1;
            if (lna.role.type == 'EVF') return 1;
            if (lnb.role.type == 'Country') return 1;
            return -1; // a = Org, b = EVF
        }
        if (lna.role.name && lnb.role.name) {
            return lna.role.name > lnb.role.name ? 1 : -1;
        }
        return 0;
    });
});

const sortedTemplates:Ref<Array<CountPerTemplate>> = computed(() => {
    return accreditations.accreditationData.templates.sort((lna:CountPerTemplate, lnb:CountPerTemplate) => {
        if (!lna.template && !lnb.template) return 0;
        if (!lna.template) return 1;
        if (!lnb.template) return -1;
        if (lna.template.name && lnb.template.name) {
            return lna.template.name > lnb.template.name ? 1 : -1;
        }
        return 0;
    });
});

function reload()
{
    accreditations.getAccreditationData();
}

function regenerate()
{
    accreditations.regenerate().then(() => {
        alert('All accreditation badges are being regenerated, please wait for the process to finish');
        accreditations.getAccreditationData();
    })
    .catch((e) => {
        console.log(e);
        alert('There was an error pushing the job to the queue. Please try again or reload the page.');
    });
}

import OverviewLine from './OverviewLine.vue';
import { ElButton } from 'element-plus';
</script>
<template>
    <div class="badge-list">
        <div class="section">
            <h3>Overview by Competition</h3>
            <span class='subtext'>These are only accreditations for athletes.</span>
            <table class="style-stripes">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Reg</th>
                        <th>Accr</th>
                        <th>Open</th>
                        <th>Done</th>
                        <th>Documents</th>
                    </tr>
                </thead>
                <tbody>
                    <OverviewLine
                        v-for="line in sortedEvents"
                        :name="line.sideEvent.title
                        "
                        :a="line.accreditations || 0"
                        :r="line.registrations || 0"
                        :d="line.dirty || 0"
                        :g="line.generated || 0"
                        :docs="line.documents"
                        :key="line.sideEvent.id"/>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h3>Overview by Country</h3>
            <span class='subtext'>These are only the registrations and accreditations for athletes and people with a federative
                role (coach, head of delegation, etc). For some participants, registrations are combined into a single accreditation,
                which causes a mismatch between the number of registrations and the number of accreditations.
            </span>
            <table class="style-stripes">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Reg</th>
                        <th>Accr</th>
                        <th>Open</th>
                        <th>Done</th>
                        <th>Documents</th>
                    </tr>
                </thead>
                <tbody>
                    <OverviewLine
                        v-for="line in sortedCountries"
                        :name="line.country.name"
                        :a="line.accreditations || 0"
                        :r="line.registrations || 0"
                        :d="line.dirty || 0"
                        :g="line.generated || 0"
                        :docs="line.documents"
                        :key="line.country.id"/>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h3>Overview by Role</h3>
            <span class="subtext">
                For some roles, registrations are combined into a single accreditation, which causes a mismatch between the number of
                registrations and the number of accreditations. Athlete 'roles' are left out.
            </span>
            <table class="style-stripes">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Reg</th>
                        <th>Accr</th>
                        <th>Open</th>
                        <th>Done</th>
                        <th>Documents</th>
                    </tr>
                </thead>
                <tbody>
                    <OverviewLine
                        v-for="line in sortedRoles"
                        :name="line.role.name || ''"
                        :a="line.accreditations || 0"
                        :r="line.registrations || 0"
                        :d="line.dirty || 0"
                        :g="line.generated || 0"
                        :docs="line.documents"
                        :key="line.role.id || 0"/>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h3>Overview by Template</h3>
            <span class="subtext">The athlete template is left out. This only displays accreditations, not registrations. </span>
            <table class="style-stripes">
                <thead>
                    <tr>
                        <th>Template</th>
                        <th>Reg</th>
                        <th>Accr</th>
                        <th>Open</th>
                        <th>Done</th>
                        <th>Documents</th>
                    </tr>
                </thead>
                <tbody>
                    <OverviewLine
                        v-for="line in sortedTemplates"
                        :name="line.template.name"
                        :a="line.accreditations || 0"
                        :r="line.registrations || 0"
                        :d="line.dirty || 0"
                        :g="line.generated"
                        :docs="line.documents || 0"
                        :key="line.template.id"/>
                </tbody>
            </table>
        </div>

        <div class="badge-footer">
            <ElButton @click="regenerate" type="primary">Regenerate</ElButton>
            <ElButton @click="reload" type="primary">Reload</ElButton>
        </div>
    </div>
</template>