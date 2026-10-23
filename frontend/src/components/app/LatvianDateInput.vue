<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import { formatLatvianDate, localizeValidity, parseLatvianDate } from '../../services/latvianValidation';

defineOptions({ inheritAttrs: false });
const props = defineProps<{ modelValue: string; min?: string; max?: string; disabled?: boolean }>();
const emit = defineEmits<{ 'update:modelValue': [value: string] }>();
const input = ref<HTMLInputElement>();
const menu = ref(false);
const text = ref(formatLatvianDate(props.modelValue));
const selected = computed(() => props.modelValue ? new Date(`${props.modelValue}T12:00:00`) : null);
watch(() => props.modelValue, async (value) => {
  text.value = formatLatvianDate(value);
  await nextTick();
  if (input.value) localizeValidity(input.value);
});
watch(() => [props.min, props.max], async () => { await nextTick(); if (input.value) localizeValidity(input.value); });
function typeDate(event: Event) {
  text.value = (event.target as HTMLInputElement).value;
  const value = parseLatvianDate(text.value);
  // Keep incomplete input visible; native constraint validation blocks submission.
  if (value !== null || !text.value) emit('update:modelValue', value || '');
  if (input.value) localizeValidity(input.value);
}
async function selectDate(value: unknown) {
  if (!(value instanceof Date)) return;
  const iso = `${String(value.getFullYear()).padStart(4, '0')}-${String(value.getMonth() + 1).padStart(2, '0')}-${String(value.getDate()).padStart(2, '0')}`;
  emit('update:modelValue', iso);
  text.value = formatLatvianDate(iso);
  menu.value = false;
  await nextTick();
  if (input.value) { localizeValidity(input.value); input.value.focus(); }
}
</script>

<template>
  <span class="latvian-date-input">
    <input ref="input" v-bind="$attrs" :value="text" type="text" inputmode="numeric"
      placeholder="dd.mm.gggg" maxlength="11" :disabled="disabled"
      data-latvian-date :data-date-min="min" :data-date-max="max" @input="typeDate" />
    <v-menu v-model="menu" :close-on-content-click="false" :disabled="disabled" :max-width="'calc(100vw - 24px)'">
      <template #activator="{ props: activator }">
        <button v-bind="activator" class="date-calendar-button" type="button" :disabled="disabled" aria-label="Izvēlēties datumu">
          <i class="mdi mdi-calendar-month-outline" aria-hidden="true" />
        </button>
      </template>
      <v-date-picker :model-value="selected" :min="min" :max="max" :first-day-of-week="1"
        hide-header show-adjacent-months theme="light" color="#245bf5" @update:model-value="selectDate" />
    </v-menu>
  </span>
</template>

<style scoped>
.latvian-date-input { display: block; position: relative; min-width: 0; width: 100%; }
.latvian-date-input input { padding-right: 44px; }
.date-calendar-button { position: absolute; right: 1px; top: 1px; width: 43px; height: calc(100% - 2px); display: grid; place-items: center; color: inherit; border-radius: 7px; }
</style>
