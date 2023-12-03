<script lang="ts" setup>
import type { RoleSchema } from '../../../../common/api/schemas/role';
import { useDataStore } from '../../stores/data';
const data = useDataStore();

function getLines(part:string, role: RoleSchema)
{
    var rkey = 'r' + (role.id || 0);
    var total = 0;
    if (data.overviewData) {
        data.overviewData.forEach((line) => {
            if (line.country == part && line.counts[rkey]) {
                total = line.counts[rkey][0];
            }
        });
    }
    return {
        role: role,
        count: total
    };
}
import RoleLine from './RoleLine.vue';
</script>
<template>
    <div class="organisation-overview">
      <h5 class="block-title">Organisation</h5>
      <table class='style-stripes'>
        <thead>
            <tr>
                <th>Role</th>
                <th>Registrations</th>
            </tr>
        </thead>
        <tbody>
            <RoleLine v-for="role in data.organisationRoles" :key="role.id || 0" :line="getLines('corg', role)" />
        </tbody>
      </table>
      <h5 class="block-title">Officials</h5>
      <table class='style-stripes'>
        <thead>
            <tr>
                <th>Role</th>
                <th>Registrations</th>
            </tr>
        </thead>
        <tbody>
            <RoleLine v-for="role in data.officialRoles" :key="role.id || 0" :line="getLines('coff', role)" />
        </tbody>
      </table>
    </div>
</template>