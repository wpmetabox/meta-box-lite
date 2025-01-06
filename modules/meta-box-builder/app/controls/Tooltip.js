import { Dashicon, Tooltip as T } from "@wordpress/components";

const Tooltip = ( { content } ) => (
  <T text={ content } delay={ 0 } position="top">
    <span className="og-tooltip-icon"><Dashicon icon="editor-help" /></span>
  </T>
);
export default Tooltip;