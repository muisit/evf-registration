import { fileURLToPath, URL } from 'node:url'

import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import ElementPlus from 'unplugin-element-plus/vite'

// https://vitejs.dev/config/
export default defineConfig(({command, mode}) => {
    const env = loadEnv(mode, process.cwd(), '');
    return {
        css: {
            preprocessorOptions: {
                scss: {
                    additionalData: `@use "../common/assets/theming.scss" as *;`,
                },
            },
        },
        plugins: [
            vue(),
            vueJsx(),
            ElementPlus({
                useSource: true
            }),
        ],
        resolve: {
            alias: {
                '@': fileURLToPath(new URL('./src', import.meta.url))
            }
        },
        server: {
            port: env.SERVERPORT,
            strictPort: true
        }
    };
})
