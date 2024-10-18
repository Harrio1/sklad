import IMask from 'imask';

export default {
  mounted(el, binding) {
    const mask = IMask(el, binding.value);
    el._imask = mask;

    // Обновляем значение маски при изменении v-model
    el.addEventListener('input', () => {
      mask.updateValue();
    });
  },
  updated(el, binding) {
    if (el._imask && el._imask.masked.rawInputValue !== el.value) {
      el._imask.value = el.value;
    }
  },
  unmounted(el) {
    if (el._imask) {
      el._imask.destroy();
      delete el._imask;
    }
  }
};
