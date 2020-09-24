<template>
  <button
    type="button"
    v-on:click.prevent="toggleAutosave"
    :class="{'bg-teal-500': autosaveProc === 0, 'bg-red-500': autosaveProc > 0}"
    class="block text-white rounded py-2 px-4 mr-2"

  >{{ buttonText }}</button>
</template>

<script>
import axios from 'axios';

export default {
  name: 'StoryAutoSaveButton',
  props: {
    contentList: Array,
    triggerForceSave: Boolean,
    triggerAutoSave: Boolean
  },
  data() {
    return {
      formMethod: 'POST',
      formAction: '',
      csrfToken: '',
      formData: {},
      isSaving: false,
      autosaveProc: 0,
      hasError: false,
    }
  },
  methods: {
    toggleAutosave() {
      if (this.isSaving) {
        alert('Cannot disable while saving is in progress...');
        return;
      }
      if (this.autosaveProc) {
        clearInterval(this.autosaveProc);
        this.autosaveProc = 0;
        this.isSaving = false;
        this.hasError = false;
      } else {
        this.autosaveProc = setInterval(
          this.processAutosave,
          10 * 1000
        );
      }
    },
    processAutosave() {
      if (!this.isSaving) {
        this.isSaving = true;
        this.gatherData();
        setTimeout(this.submitForm, 500);
      }
    },
    submitForm() {
      return axios.post(
        this.formAction,
        this.formData
      ).then(() => {
        this.hasError = false;
      }).catch((error) => {
        this.hasError = true;
        throw error;
      }).finally(() => {
        this.isSaving = false;
      });
    },
    gatherData() {
      const form = document.getElementById('story-form');
      this.formAction = form.action;
      if ('_method' in form.elements) {
        this.formMethod = form.elements._method.value;
      }
      let data = {};
      for (let e of form.elements) {
        if(e.name.length > 1 &&
           e.name.startsWith('body') == false &&
           e.name.startsWith('tags_create') == false) // Autosave should not create tags...
        {
          if (e.type == 'radio') {
            if (e.checked === true) {
              data[e.name] = e.value;
            }
          } else if (e.type == 'checkbox') {
            if (e.checked === true) {
              let cbox_name = e.name.replace('[]', '');
              if (cbox_name in data) {
                data[cbox_name].push(e.value);
              } else {
                data[cbox_name] = [e.value];
              }
            }
          } else {
            data[e.name] = e.value;
          }
        }
        this.formData = data;
        this.formData['body'] = this.contentList;
      }
    },
  },
  computed: {
    buttonText() {
      if (this.isSaving) {
        return 'Saving...';
      }
      if (this.autosaveProc > 0) {
        if (this.hasError) {
          return 'Last save attempt failed...';
        }
        return 'Disable Autosave';
      }
      return 'Enable Autosave';
    }
  },
  watch: {
    triggerForceSave() {
      if (this.triggerForceSave) {
        this.gatherData();
        setTimeout(() => {
          this.submitForm().then((response) => {
            alert('Saved successfully!');
          }).catch((response) => {
            alert('Failed to save.');
          });
        }, 500);
      }
    },
    triggerAutoSave() {
      this.toggleAutosave();
      if (this.autosaveProc) {
        alert('Enabled autosave');
      } else {
        alert('Disabled autosave');
      }
    }
  }
}
</script>
