/**
 * Tool for creating video Blocks for Editor.js
 * IDEESCULTURE 2021, edited version by Gautier MICHELIN
 *
 * @typedef {object} VideoToolData — Input/Output data format for our Tool
 * @property {string} url - image source URL
 * @property {string} caption - image caption
 *
 * @typedef {object} VideoToolConfig
 * @property {string} placeholder — custom placeholder for URL field
 */
class SimpleVideo {
  /**
   * Our tool should be placed at the Toolbox, so describe an icon and title
   */
  static get toolbox() {
    return {
      title: 'Video',
      icon: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"· viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><path d="M0,62.3v387.5h512V62.3H0z M62.8,125.1h386.4v261.9H62.8V125.1z M198.6,170.7v174.4l150.8-87.5L198.6,170.7z"/></g></svg>'
    };
  }

  /**
   * Allow render Image Blocks by pasting HTML tags, files and URLs
   * @see {@link https://editorjs.io/paste-substitutions}
   * @return {{tags: string[], files: {mimeTypes: string[], extensions: string[]}, patterns: {image: RegExp}}}
   */
  static get pasteConfig() {
    return {
      tags: ['VIDEO'],
      files: {
        mimeTypes: ['video/*'],
        extensions: ['mp4'] // You can specify extensions instead of mime-types
      },
      patterns: {
        image: /https?:\/\/\S+\.(mp4)$/i
      }
    };
  }

  /**
   * Automatic sanitize config
   * @see {@link https://editorjs.io/sanitize-saved-data}
   */
  static get sanitize(){
    return {
      url: {},
      caption: {
        b: true,
        a: {
          href: true
        },
        i: true
      }
    };
  }

  /**
   * Tool class constructor
   * @param {VideoToolData} data — previously saved data
   * @param {object} api — Editor.js Core API {@link  https://editorjs.io/api}
   * @param {VideoToolConfig} config — custom config that we provide to our tool's user
   */
  constructor({data, api, config}){
    this.api = api;
    this.config = config || {};
    this.data = {
      url: data.url || '',
      caption: data.caption || ''
    };

    this.wrapper = undefined;
    this.settings = [];
  }

  /**
   * Return a Tool's UI
   * @return {HTMLElement}
   */
  render(){
    this.wrapper = document.createElement('div');
    this.wrapper.classList.add('simple-video');

    if (this.data && this.data.url){
      this._createVideo(this.data.url, this.data.caption);
      return this.wrapper;
    }

    const input = document.createElement('input');

    input.placeholder = this.config.placeholder || 'Paste a video URL...';
    input.addEventListener('paste', (event) => {
      this._createVideo(event.clipboardData.getData('text'));
    });

    this.wrapper.appendChild(input);

    return this.wrapper;
  }

  /**
   * @private
   * Create video tag with player
   * @param {string} url — image source
   * @param {string} captionText — caption value
   */
  _createVideo(url, captionText){
    const video = document.createElement('video');
    video.setAttribute('controls', 'controls');
    const source = document.createElement('source');
    source.setAttribute('type', 'video/mp4');
    const caption = document.createElement('div');
    //caption.setAttribute('placeholder', 'Enter a title or a description');

    source.src = url;
    video.appendChild(source);
    caption.contentEditable = true;
    caption.innerHTML = captionText || '';

    this.wrapper.innerHTML = '';
    this.wrapper.appendChild(video);
    this.wrapper.appendChild(caption);

    this._acceptTuneView();
  }

  /**
   * Extract data from the UI
   * @param {HTMLElement} blockContent — element returned by render method
   * @return {SimpleVideoData}
   */
  save(blockContent){
    const video = blockContent.querySelector('video');
    const source = video.querySelector('source');
    const caption = blockContent.querySelector('[contenteditable]');

    return Object.assign(this.data, {
      url: source.src,
      caption: caption.innerHTML || ''
    });
  }

  /**
   * Skip empty blocks
   * @see {@link https://editorjs.io/saved-data-validation}
   * @param {VideoToolConfig} savedData
   * @return {boolean}
   */
  validate(savedData){
    if (!savedData.url.trim()){
      return false;
    }

    return true;
  }

  s = 'simple-video';

  /**
   * Making a Block settings: 'add border', 'add background', 'stretch to full width'
   * @see https://editorjs.io/making-a-block-settings — tutorial
   * @see https://editorjs.io/tools-api#rendersettings - API method description
   * @return {HTMLDivElement}
   */
  renderSettings(){
    const wrapper = document.createElement('div');

    this.settings.forEach( tune => {
      let button = document.createElement('div');

      button.classList.add(this.api.styles.settingsButton);
      button.classList.toggle(this.api.styles.settingsButtonActive, this.data[tune.name]);
      button.innerHTML = tune.icon;
      wrapper.appendChild(button);

      button.addEventListener('click', () => {
        this._toggleTune(tune.name);
        button.classList.toggle(this.api.styles.settingsButtonActive);
      });

    });

    return wrapper;
  }

  /**
   * @private
   * Click on the Settings Button
   * @param {string} tune — tune name from this.settings
   */
  _toggleTune(tune) {
    this.data[tune] = !this.data[tune];
    this._acceptTuneView();
  }

  /**
   * Add specified class corresponds with activated tunes
   * @private
   */
  _acceptTuneView() {
    this.settings.forEach( tune => {
      this.wrapper.classList.toggle(tune.name, !!this.data[tune.name]);

      if (tune.name === 'stretched') {
        this.api.blocks.stretchBlock(this.api.blocks.getCurrentBlockIndex(), !!this.data.stretched);
      }
    });
  }

  /**
   * Handle paste event
   * @see https://editorjs.io/tools-api#onpaste - API description
   * @param {CustomEvent }event
   */
  onPaste(event){
    switch (event.type){
      case 'tag':
        const video = event.detail.data;
        console.log('video tag pasted', event.detail.data);
        //this._createVideo(imgTag.src);
        break;
      case 'file':
        /* We need to read file here as base64 string */
        const file = event.detail.file;
        const reader = new FileReader();

        reader.onload = (loadEvent) => {
          this._createVideo(loadEvent.target.result);
        };

        reader.readAsDataURL(file);
        break;
      case 'pattern':
        const src = event.detail.data;

        this._createVideo(src);
        break;
    }
  }
}