<template>
  <div>
    <story-content-item
      v-for="(item,index) in contentList"
      :key="item.id"
      :contentData="item"
      :contentIndex="index"
      @request-save-content="triggerForcedAutosave"
      @request-autosave="triggerAutosave"
      @triggered-remove-method="removeItem"
      @updated-content-item="updateContentList"
    />

    <div class="flex" style="margin-top: -50px">
      <story-auto-save-button
        v-if="isEditing"
        :contentList="contentList"
        :triggerForceSave="triggerForceSave"
        :triggerAutoSave="triggerAutoSave">
      </story-auto-save-button>

      <button
        type="button"
        class="inline-block bg-teal-500 hover:bg-teal-700 text-white rounded py-2 px-4"
        v-on:click.prevent="appendItem">
        Add more content
      </button>
    </div>

  </div>
</template>

<script>
import StoryContentItem from './StoryContentItem.vue';

export default {
  name: 'StoryContentList',
  components: {
    StoryContentItem
  },
  props: {
    contentListDefault: Array,
    isEditing: Boolean
  },
  data() {
    return {
      contentList: [],
      contentListMeta: {
        name: "",
        content: "",
        format: "plaintext"
      },
      contentIndexSeq: 1000,
      triggerForceSave: false,
      triggerAutoSave: false
    }
  },
  created() {
    if (this.contentListDefault.length && Array.isArray(this.contentListDefault)) {
      this.contentListDefault.forEach(element => {
        this.contentList.push(
          { id: this.contentIndexSeq, ...element }
        );
        this.contentIndexSeq++;
      });
    } else {
      this.appendItem();
    }
  },
  methods: {
    appendItem() {
      let cont = {...this.contentListMeta};
      cont.id = this.contentIndexSeq;
      this.contentIndexSeq++;
      this.contentList.push(cont);
    },
    removeItem(pos) {
      if (pos) {
        let val = this.contentList[pos].content;
        if (val !== null && val.length > 0) {
          alert('Please clear textarea content first.');
          return;
        }
        this.contentList.splice(pos, 1);
      }
    },
    updateContentList(editorModel) {
      this.contentList[editorModel.index] = editorModel.data;
    },
    triggerForcedAutosave() {
      if (!this.triggerForceSave) {
        this.triggerForceSave = true;
        setTimeout(() => this.triggerForceSave = false, 5000); // Can only save every 5 seconds
      }
    },
    triggerAutosave() {
      this.triggerAutoSave = !this.triggerAutoSave;
    }
  }
}
</script>
