<template>
  <div class="story-content-item block my-6">

    <div class="mb-2" v-if="contentIndex > 0">
      <label class="block uppercase text-gray-600 text-sm mb-2">
          {{ contentIndex+1 }} : Name
      </label>

      <input
        :name="nameInputId"
        v-model="editor.name"
        class="appearance-none border rounded w-full p-2 bg-gray-200 text-gray-700 leading-tight font-mono"
        type="text"
        placeholder="Unique content name"
      />
    </div>

    <div class="mb-2">
      <div class="block mb-2">
          <span
            v-if="contentIndex == 0"
            class="mr-2 uppercase text-gray-600 text-sm">
            1 : Default Content
          </span>

          <div class="inline-block">
            <label
              v-for="cf in contentFormats"
              :key="cf"
              class="inline-flex items-center mr-1 rounded-full bg-gray-200 py-1 px-3"
            >
              <input
                v-model="editor.format"
                :name="formatRadioId"
                :value="cf"
                type="radio"  class="form-radio"
              />
              <span class="ml-2 text-gray-600 uppercase text-xs cursor-pointer">{{  cf }}</span>
            </label>
          </div>
      </div>

      <span
        v-on:click="maxTextEditor"
        v-show="editorMaxModel"
        class="story-editor-vue-max-close-btn"
      >Min</span>

      <textarea
        v-model="editor.content"
        v-on:keydown.ctrl.83.exact.prevent="keySaveContent"
        v-on:keydown.ctrl.shift.83.exact.prevent="keyAutosave"
        v-on:keydown.ctrl.shift.70.exact.prevent="keyMaxTextEditor"
        ref="texteditor"
        :name="editorInputId"
        class="appearance-none border rounded w-full p-4 bg-gray-100 text-gray-900 leading-tight font-mono tracking-tight"
        :class="{'story-editor-vue-max-textarea': editorMaxModel}"
        :rows="20"></textarea>

      <div class="text-right pt-1">
        <div
          v-on:click="maxTextEditor"
          class="py-2 px-4 rounded-full inline-block hover:bg-gray-300 text-sm uppercase text-gray-600 cursor-pointer"
        >Max</div>
        <div
          v-if="contentIndex > 0"
          v-on:click="triggerRemoveMethod"
          class="py-2 px-4 rounded-full inline-block text-sm text-red-500 bg-red-200 cursor-pointer"
        >Remove</div>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'StoryContentItem',
  props: {
    contentData: Object,
    contentIndex: Number
  },
  data() {
    return {
      editor: {
        name: '',
        content: '',
        format: '',
      },
      editorMaxModel: false,
      editorOffset: 0,
      contentFormats: [
        'plaintext',
        'html',
        'markdown',
        'json'
      ]
    }
  },
  created() {
    this.editor = {...this.contentData};
  },
  methods: {
    triggerRemoveMethod() {
      this.$emit('triggered-remove-method', this.contentIndex)
    },
    maxTextEditor(event) {
      this.editorMaxModel = !this.editorMaxModel;

      if (this.editorMaxModel) {
        this.editorOffset = this.$refs.texteditor.offsetTop - 50;
        this.$refs.texteditor.focus();
      }

      if (this.editorMaxModel) {
        document.body.classList.add('story-editor-vue-max');
        window.scrollTo(0, 0);
      } else {
        document.body.classList.remove('story-editor-vue-max');
        window.scrollTo(0, this.editorOffset);
      }

    },
    keyMaxTextEditor(event) {
      this.maxTextEditor(event);
    },
    keySaveContent() {
      this.$emit('request-save-content');
    },
    keyAutosave() {
      this.$emit('request-autosave');
    }
  },
  computed: {
    nameInputId() {
      return 'body[' + this.contentIndex + '][name]';
    },
    editorInputId() {
      return 'body[' + this.contentIndex + '][content]';
    },
    formatRadioId() {
      return 'body[' + this.contentIndex + '][format]';
    }
  },
  watch: {
    editor(updatedModel) {
      this.$emit('updated-content-item', {
        index: this.contentIndex,
        data: updatedModel
      });
    }
  }
}
</script>

<style class="scss">
.story-editor-vue-max-textarea {
  position: absolute;
  z-index: 100;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  border-radius: 0 !important;
}
.story-editor-vue-max-close-btn {
  position: absolute;
  z-index: 150;
  top: 0;
  right: 0;
  background-color: #ff0000;
  color: #fff;
  font-size: 9pt;
  font-weight: bold;
  text-transform: uppercase;
  cursor: pointer;
  padding: 4px;
  display: block;
}
.story-editor-vue-max {
  overflow: hidden;
  height: 100%;
  width: 100%;
}
</style>
