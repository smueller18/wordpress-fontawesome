import React, { useState } from 'react'
import { useSelector } from 'react-redux'
import Options from './Options'
import KitsConfigView from './KitsConfigView'
import sharedStyles from './App.module.css'
import optionStyles from './Options.module.css'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faDotCircle } from '@fortawesome/free-solid-svg-icons'
import { faCircle } from '@fortawesome/free-regular-svg-icons'
import classnames from 'classnames'
import styles from './SettingsTab.module.css'

export default function SettingsTab() {
  const alreadyUsingKit = useSelector( state => !!state.options.kitToken )
  const [useKits, setUseKits] = useState(alreadyUsingKit)

  return <div>
    <div className={ styles['select-config-container'] }>
      <div>
        <input
          id="select_use_kits"
          name="select_use_kits"
          type="radio"
          value={ useKits }
          checked={ useKits }
          onChange={ () => setUseKits(! useKits) }
          className={ classnames(sharedStyles['sr-only'], sharedStyles['input-radio-custom']) }
        />
        <label htmlFor="select_use_kits" className={ optionStyles['option-label'] }>
          <span className={ sharedStyles['relative'] }>
            <FontAwesomeIcon
              icon={ faDotCircle }
              className={ sharedStyles['checked-icon'] }
              size="lg"
              fixedWidth
            />
            <FontAwesomeIcon
              icon={ faCircle }
              className={ sharedStyles['unchecked-icon'] }
              size="lg"
              fixedWidth
            />
          </span>
          <span className={ optionStyles['option-label-text'] }>
          Use A Kit
          </span>
        </label>
      </div>
      <div>
        <input
          id="select_use_cdn"
          name="select_use_cdn"
          type="radio"
          value={ ! useKits }
          checked={ ! useKits }
          onChange={ () => setUseKits(! useKits) }
          className={ classnames(sharedStyles['sr-only'], sharedStyles['input-radio-custom']) }
        />
        <label htmlFor="select_use_cdn" className={ optionStyles['option-label'] }>
          <span className={ sharedStyles['relative'] }>
            <FontAwesomeIcon
              icon={ faDotCircle }
              className={ sharedStyles['checked-icon'] }
              size="lg"
              fixedWidth
            />
            <FontAwesomeIcon
              icon={ faCircle }
              className={ sharedStyles['unchecked-icon'] }
              size="lg"
              fixedWidth
            />
          </span>
          <span className={ optionStyles['option-label-text'] }>
          Use CDN
          </span>
        </label>
      </div>
    </div>
    {
      useKits
      ? <KitsConfigView />
      : <Options />
    }
  </div>
}
