<template>
    <div class="retry-schedule-compact">
        <label class="block text-sm font-medium text-gray-700 mb-3">
            Retry Schedule (Hours between attempts)
        </label>
        <div class="grid grid-cols-3 gap-2">
            <div v-for="(interval, index) in retrySchedule" :key="index" class="retry-slot-compact">
                <label class="block text-xs text-gray-600 mb-1">
                    #{{ index + 1 }}
                </label>
                <InputNumber
                    v-model="retrySchedule[index]"
                    :min="1"
                    :max="168"
                    :suffix="'h'"
                    class="w-full text-sm"
                    placeholder="Hours"
                />
            </div>
        </div>
        <div class="mt-3 flex gap-2 flex-wrap">
            <button
                type="button"
                @click="resetToDefault"
                class="text-xs text-blue-600 hover:text-blue-800 underline"
            >
                Reset to Default
            </button>
            <button
                type="button"
                @click="setAllSame"
                class="text-xs text-blue-600 hover:text-blue-800 underline"
            >
                Set All to Same
            </button>
        </div>
        <div v-if="showSetAllInput" class="mt-2 flex items-center gap-2">
            <InputNumber
                v-model="allSameValue"
                :min="1"
                :max="168"
                :suffix="'h'"
                class="w-20 text-sm"
                placeholder="Hours"
            />
            <button
                type="button"
                @click="applyAllSame"
                class="px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600"
            >
                Apply
            </button>
        </div>
    </div>
</template>

<script>
import InputNumber from 'primevue/inputnumber';

const DEFAULT_SCHEDULE = [4, 12, 24, 24, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5];

export default {
    name: 'RetryScheduleCompact',
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
        const initialSchedule = Array.isArray(this.modelValue) && this.modelValue.length > 0
            ? this.modelValue.map(Number)
            : [...DEFAULT_SCHEDULE];

        return {
            retrySchedule: initialSchedule,
            showSetAllInput: false,
            allSameValue: 5
        };
    },
    watch: {
        modelValue: {
            handler(newVal) {
                const normalized = this.normalizeSchedule(newVal);

                if (!this.areSchedulesEqual(this.retrySchedule, normalized)) {
                    this.retrySchedule = [...normalized];
                }
            },
            immediate: true,
            deep: true
        },
        retrySchedule: {
            handler(newVal) {
                if (!this.areSchedulesEqual(newVal, this.modelValue ?? [])) {
                    this.$emit('update:modelValue', [...newVal]);
                }
            },
            deep: true
        }
    },
    methods: {
        getDefaultSchedule() {
            return [...DEFAULT_SCHEDULE];
        },
        normalizeSchedule(value) {
            if (Array.isArray(value) && value.length > 0) {
                return value.map(Number);
            }

            return this.getDefaultSchedule();
        },
        areSchedulesEqual(a = [], b = []) {
            if (!Array.isArray(a) || !Array.isArray(b)) {
                return false;
            }

            if (a.length !== b.length) {
                return false;
            }

            return a.every((value, index) => value === b[index]);
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
.retry-schedule-compact {
    @apply p-3 border border-gray-200 rounded-lg bg-gray-50;
}

.retry-slot-compact {
    @apply flex flex-col;
}

.retry-slot-compact label {
    @apply text-xs font-medium text-gray-600;
}
</style>
