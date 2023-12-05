<script lang="ts" setup>
import { reactive, ref } from 'vue';
const emits = defineEmits(['onClose','onSave']);
import { useAuthStore } from '../../../common/stores/auth';

const auth = useAuthStore();
const isVisible = ref(true);

const form = reactive({
  name: '',
  password: '',
});

function closeForm()
{
    emits('onClose');
}

function submitForm()
{
    if (form.password.length < 8) {
        alert('Please enter a valid password of at least 8 characters');
    }
    if (form.name.length < 3) {
        alert('Please enter a valid e-mail address or username');
    }
    emits('onSave', {username: form.name, password: form.password});
    closeForm();
}

import { ElDialog, ElForm, ElFormItem, ElInput, ElButton } from 'element-plus';
</script>
<template>
    <ElDialog v-model="isVisible" title="Login" :close-on-press-escape="false" :close-on-click-modal="false" :show-close="false">
      <p>To use this application, please enter your username or e-mail address connected to your
        website account on the <a href="https://www.veteransfencing.eu">EVF</a> website.<br/>
        If you do not remember that password, please reset the password first using 
        the 'Lost your Password' option on the <a href="https://www.veteransfencing.eu/wp-login.php?action=lostpassword">login page</a>.
      </p>
      <ElForm class="login">
        <ElFormItem label="Name/E-mail address">
          <ElInput v-model="form.name" />
        </ElFormItem>
        <ElFormItem label="Password">
          <ElInput type="password" v-model="form.password" show-password/>
        </ElFormItem>
      </ElForm>
      <template #footer>
        <span class="dialog-footer">
          <ElButton type="primary" @click="submitForm" :disabled="auth.isLoading">Log in</ElButton>
        </span>
      </template>
    </ElDialog>
</template>