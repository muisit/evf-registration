<script lang="ts" setup>
import  { ElTabs, ElTabPane } from 'element-plus';
</script>
<template>
    <ElTabs type="card">
        <ElTabPane label="Overview" name="overview">Pane 1</ElTabPane>
        <ElTabPane label="Registration" name="registration">Pane 2</ElTabPane>
        <ElTabPane label="Cashier" name="cashier">Pane 3</ElTabPane>
        <ElTabPane label="Badges" name="badges">Pane 4</ElTabPane>
        <ElTabPane label="Actions" name="actions">Pane 5</ElTabPane>
        <ElTabPane label="Templates" name="templates">Pane 6</ElTabPane>
    </ElTabs>
    
</template>