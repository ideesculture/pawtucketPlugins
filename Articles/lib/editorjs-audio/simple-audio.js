/**
 * Tool for creating audio Blocks for Editor.js
 * IDEESCULTURE 2021, edited version by Gautier MICHELIN
 *
 * @typedef {object} AudioToolData — Input/Output data format for our Tool
 * @property {string} url - image source URL
 * @property {string} caption - image caption
 *
 * @typedef {object} AudioToolConfig
 * @property {string} placeholder — custom placeholder for URL field
 */
class SimpleAudio {
  /**
   * Our tool should be placed at the Toolbox, so describe an icon and title
   */
  static get toolbox() {
    return {
      title: 'Audio',
      icon: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><path d="M99.7,99.9L75.3,75.5L75,75.8c-46.3,46.3-75,110.3-75,181c0,70.3,28.4,134,74.3,180.3l0,0l24.4-24.4c-39.7-40-64.2-95.1-64.2-155.9C34.4,195.4,59.3,139.9,99.7,99.9z"/><path d="M437,75.8l-1.3-1.3l-24.4,24.4c40.9,40.2,66.3,96.1,66.3,157.9c0,61-24.7,116.3-64.7,156.3l24.4,24.4c46.1-46.3,74.7-110.2,74.7-180.7C512,186.1,483.4,122.1,437,75.8z"/><path d="M145.6,145.7c-28.6,28.3-46.3,67.7-46.3,111.1c0,42.9,17.3,81.8,45.2,110l0,0l24.3-24.3c-21.7-22-35.1-52.3-35.1-85.7c0-33.9,13.9-64.6,36.2-86.8L145.6,145.7z"/><path d="M341.2,169c22.9,22.2,37.2,53.3,37.2,87.7c0,33.6-13.6,64.1-35.5,86.1l24.3,24.3c28.2-28.3,45.7-67.3,45.7-110.4c0-43.9-18.1-83.6-47.2-112l0,0L341.2,169z"/><path d="M297.2,213.1c-10.7-10.1-25.2-16.3-41.1-16.3c-33.2,0-60,26.9-60,60c0,33.2,26.9,60,60,60c33.2,0,60-26.9,60-60v0C316.1,239.6,308.9,224,297.2,213.1L297.2,213.1L297.2,213.1z M256,282.6c-14.2,0-25.8-11.5-25.8-25.8c0-14.2,11.5-25.8,25.8-25.8c14.2,0,25.8,11.5,25.8,25.8C281.8,271,270.2,282.6,256,282.6z"/></g></svg>'
    };
  }

  /**
   * Allow render Image Blocks by pasting HTML tags, files and URLs
   * @see {@link https://editorjs.io/paste-substitutions}
   * @return {{tags: string[], files: {mimeTypes: string[], extensions: string[]}, patterns: {image: RegExp}}}
   */
  static get pasteConfig() {
    return {
      tags: ['AUDIO'],
      files: {
        mimeTypes: ['audio/*'],
        extensions: ['wav', 'mp3'] // You can specify extensions instead of mime-types
      },
      patterns: {
        image: /https?:\/\/\S+\.(wav|mp3)$/i
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
   * @param {AudioToolData} data — previously saved data
   * @param {object} api — Editor.js Core API {@link  https://editorjs.io/api}
   * @param {AudioToolConfig} config — custom config that we provide to our tool's user
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
    this.wrapper.classList.add('simple-audio');

    if (this.data && this.data.url){
      this._createAudio(this.data.url, this.data.caption);
      return this.wrapper;
    }

    const input = document.createElement('input');

    input.placeholder = this.config.placeholder || 'Paste an audio URL...';
    input.addEventListener('paste', (event) => {
      this._createAudio(event.clipboardData.getData('text'));
    });

    this.wrapper.appendChild(input);

    return this.wrapper;
  }

  /**
   * @private
   * Create audio tag with player
   * @param {string} url — image source
   * @param {string} captionText — caption value
   */
  _createAudio(url, captionText){
    const audio = document.createElement('audio');
    audio.setAttribute('controls', 'controls');
    const source = document.createElement('source');
    const caption = document.createElement('div');
    //caption.setAttribute('placeholder', 'Enter a title or a description');

    source.src = url;
    audio.appendChild(source);
    caption.contentEditable = true;
    caption.innerHTML = captionText || '';

    this.wrapper.innerHTML = '';
    this.wrapper.appendChild(audio);
    this.wrapper.appendChild(caption);

    this._acceptTuneView();
  }

  /**
   * Extract data from the UI
   * @param {HTMLElement} blockContent — element returned by render method
   * @return {SimpleAudioData}
   */
  save(blockContent){
    const audio = blockContent.querySelector('audio');
    const source = audio.querySelector('source');
    const caption = blockContent.querySelector('[contenteditable]');

    return Object.assign(this.data, {
      url: source.src,
      caption: caption.innerHTML || ''
    });
  }

  /**
   * Skip empty blocks
   * @see {@link https://editorjs.io/saved-data-validation}
   * @param {AudioToolConfig} savedData
   * @return {boolean}
   */
  validate(savedData){
    if (!savedData.url.trim()){
      return false;
    }

    return true;
  }

  s = 'simple-audio';

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
        $('.' + this.s).parent().removeClass('floatRight');
        $('.' + this.s).parent().removeClass('floatLeft');
        $('.simple-audio.floatLeft').parent().addClass('floatLeft');
        $('.simple-audio.floatRight').parent().addClass('floatRight');
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
        const audio = event.detail.data;
        console.log('audio tag pasted', event.detail.data);
        //this._createAudio(imgTag.src);
        break;
      case 'file':
        /* We need to read file here as base64 string */
        const file = event.detail.file;
        const reader = new FileReader();

        reader.onload = (loadEvent) => {
          this._createAudio(loadEvent.target.result);
        };

        reader.readAsDataURL(file);
        break;
      case 'pattern':
        const src = event.detail.data;

        this._createAudio(src);
        break;
    }
  }
}