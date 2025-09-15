<template>
    <div class="retry-schedule-component">
        <label class="block text-sm font-medium text-gray-700 mb-4">
            Retry Schedule (Hours between attempts)
        </label>
        <div class="grid grid-cols-5 gap-4">
            <div v-for="(interval, index) in retrySchedule" :key="index" class="retry-slot">
                <label class="block text-xs text-gray-600 mb-1">
                    Attempt {{ index + 1 }}
                </label>
                <InputNumber
                    v-model="retrySchedule[index]"
                    :min="1"
                    :max="168"
                    :suffix="' hours'"
                    class="w-full"
                    placeholder="Hours"
                />
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <button
                type="button"
                @click="resetToDefault"
                class="text-sm text-blue-600 hover:text-blue-800 underline"
            >
                Reset to Default
            </button>
            <button
                type="button"
                @click="setAllSame"
                class="text-sm text-blue-600 hover:text-blue-800 underline"
            >
                Set All to Same Value
            </button>
        </div>
        <div v-if="showSetAllInput" class="mt-2">
            <InputNumber
                v-model="allSameValue"
                :min="1"
                :max="168"
                :suffix="' hours'"
                class="w-32"
                placeholder="Hours"
            />
            <button
                type="button"
                @click="applyAllSame"
                class="ml-2 px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600"
            >
                Apply
            </button>
        </div>
    </div>
</template>

<script>
import InputNumber from 'primevue/inputnumber';

export default {
    name: 'RetrySchedule',
    components: {
        InputNumber
    },
    props: {
        modelValue: {
            type: Array,
            default: () => []
        }
    },
    emits: ['update:modelValue'],
    data() {
        return {
            retrySchedule: [...this.modelValue],
            showSetAllInput: false,
            allSameValue: 5
        };
    },
    watch: {
        modelValue: {
            handler(newVal) {
                if (newVal && newVal.length > 0) {
                    this.retrySchedule = [...newVal];
                } else {
                    this.retrySchedule = this.getDefaultSchedule();
                }
            },
            immediate: true
        },
        retrySchedule: {
            handler(newVal) {
                this.$emit('update:modelValue', newVal);
            },
            deep: true
        }
    },
    methods: {
        getDefaultSchedule() {
            return [4, 12, 24, 24, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5];
        },
        resetToDefault() {
            this.retrySchedule = this.getDefaultSchedule();
        },
        setAllSame() {
            this.showSetAllInput = true;
        },
        applyAllSame() {
            this.retrySchedule = new Array(15).fill(this.allSameValue);
            this.showSetAllInput = false;
        }
    }
};
</script>

<style scoped>
.retry-schedule-component {
    @apply p-4 border border-gray-200 rounded-lg bg-gray-50;
}

.retry-slot {
    @apply flex flex-col;
}

.retry-slot label {
    @apply text-xs font-medium text-gray-600;
}
</style>
