<template>
    <transition name="modal-transition">
        <div class="modal" v-if="active" @click.self="$emit('close')">
            <div :class="['modal__body', this.modalBodyClass]">
                <div class="modal__header" :class="headerClass">
                    <slot name="header"></slot>
                </div>
                <div class="modal__content">
                    <slot name="body"></slot>
                </div>
                <div class="modal__footer">
                    <slot name="footer"></slot>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
  /**
   * Abstract dialog component, solid foundation for
   * any modals and dialogs that opened over the rest page content.
   *
   * @param Vue
   */
  export default {
    props: {
      /**
       * Determines dialog visibility. This property is passed
       * from outside and cannot be changed inside dialog.
       * Dialog's consumer is responsible for manipulating dialog's visibility.
       *
       * @property {bool}
       */
      active: {
        type: Boolean
      },

      /**
       * Modal title
       *
       * @property {string}
       */
      title: {
        type: String
      },

      /**
       * Additional class modifier for modal customization.
       *
       * @property {string}
       */
      modalBodyClass: {
        type: String,
        default: ''
      },

      /**
       * Additional classes for modal header.
       *
       * @property {object|Array}
       */
      headerClass: {
        default () {
          return {}
        }
      },

      /**
       * Class that applies to the body and used
       * to prevent body's scroll catch, so long dialog can be scrolled
       * without interfering with body scroll.
       *
       * @property {string}
       */
      dialogOpenedClass: {
        type: String,
        default: 'modal-opened'
      }
    },

    watch: {
      /**
       * Watch for "active" property change and emit corresponding
       * event when it changed.
       *
       * @param isDialogActive {bool}
       */
      active (isDialogActive) {
        this.$emit(isDialogActive ? 'open' : 'close')
      }
    },

    mounted () {
      /*
       * Add body "frozen" class to the body when dialog is opened.
       */
      this.$on('open', () => {
        document.querySelector('body')
          .classList
          .add(this.dialogOpenedClass);
      });

      /*
       * Remove body "frozen" class from the body when dialog is closed.
       */
      this.$on('close', () => {
        document.querySelector('body')
          .classList
          .remove(this.dialogOpenedClass);
      });
    },
  }
</script>