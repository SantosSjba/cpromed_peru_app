<template>
  <div
    class="overflow-hidden rounded-lg"
    :class="aspectRatioClass"
  >
    <iframe
      :src="embedUrl"
      :title="title"
      frameborder="0"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
      allowfullscreen
      class="h-full w-full"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  videoId: { type: String, default: '' },
  aspectRatio: { type: String, default: '16:9' },
  title: { type: String, default: 'YouTube video' },
  class: { type: String, default: '' },
});

const aspectRatioMap = {
  '16:9': 'aspect-video',
  '4:3': 'aspect-[4/3]',
  '21:9': 'aspect-[21/9]',
  '1:1': 'aspect-square',
};

const aspectRatioClass = computed(() => aspectRatioMap[props.aspectRatio] ?? aspectRatioMap['16:9']);
const embedUrl = computed(() => (props.videoId ? `https://www.youtube.com/embed/${props.videoId}` : ''));
</script>
