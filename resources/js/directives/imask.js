import IMask from 'imask';

export default {
  mounted(el, binding) {
    IMask(el, { mask: binding.value });
  }
};