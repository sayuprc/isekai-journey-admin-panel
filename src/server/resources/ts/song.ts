interface Creator {
  creator_id: string
  creator_name: string
}

interface ArchiveType {
  archive_type: string
  archive_name: string
}

const creators = (window.creators) as Creator[]

const archiveTypes = (window.archiveTypes) as ArchiveType[]

const addLyricistBtn = document.getElementById('add_lyricist_btn') as HTMLButtonElement
const addComposerBtn = document.getElementById('add_composer_btn') as HTMLButtonElement
const addArrangerBtn = document.getElementById('add_arranger_btn') as HTMLButtonElement
const addArchiveBtn = document.getElementById('add_archive_btn') as HTMLButtonElement

const createInput = (id: string, textContent: string, type: string, value: string = ''): [HTMLLabelElement, HTMLInputElement] => {
  const label = document.createElement('label')
  label.htmlFor = id
  label.textContent = textContent

  const input = document.createElement('input')
  input.id = id
  input.name = id
  input.type = type
  input.value = value

  return [label, input]
}

const createSelect = (id: string, textContent: string, data: Creator[], selected?: string): [HTMLLabelElement, HTMLSelectElement] => {
  const label = document.createElement('label')
  label.htmlFor = id
  label.textContent = textContent

  const select = document.createElement('select')
  select.id = id
  select.name = id

  data.forEach((item) => {
    const option = document.createElement('option')
    option.value = item.creator_id
    option.textContent = item.creator_name
    if (selected === item.creator_id) {
      option.selected = true
    }
    select.appendChild(option)
  })

  return [label, select]
}

const createDeleteButton = (parent: HTMLElement, role: string, index: number): HTMLButtonElement => {
  const button = document.createElement('button')
  button.className = 'btn btn-danger'
  button.innerText = '削除'
  button.onclick = () => {
    parent.querySelector(`.${role}-${index}`)?.remove()
  }

  return button
}

const add = (parent: HTMLElement, role: string): void => {
  const count = parent.querySelectorAll(`.${role}`).length

  const div = document.createElement('div')
  div.className = `${role} ${role}-${count}`

  const [selectLabel, select] = createSelect(`${role}[${count}][creator_id]`, `クリエイター${count + 1}`, creators)
  div.appendChild(selectLabel)
  div.appendChild(select)
  const [inputLabel, input] = createInput(`${role}[${count}][order_no]`, `表示順${count + 1}`, `number`, `1`)
  div.appendChild(inputLabel)
  div.appendChild(input)
  div.appendChild(createDeleteButton(parent, role, count))

  parent.appendChild(div)
}

const addArchive = (parent: HTMLElement, role: string): void => {
  const count = parent.querySelectorAll(`.${role}`).length

  const div = document.createElement('div')
  div.className = `${role} ${role}-${count}`

  createSelectArchive(div, count, role, `${role}[${count}][archive_type]`, `種別${count + 1}`, archiveTypes)
  const [inputDateLabel, inputDate] = createInput(`${role}[${count}][archived_on]`, `日付${count + 1}`, `date`, '')
  div.appendChild(inputDateLabel)
  div.appendChild(inputDate)
  const [inputLabel, input] = createInput(`${role}[${count}][order_no]`, `表示順${count + 1}`, `number`, `1`)
  div.appendChild(inputLabel)
  div.appendChild(input)
  div.appendChild(createDeleteButton(parent, role, count))

  parent.appendChild(div)
}

const createSelectArchive = (parent: HTMLElement, count: number, role: string, id: string, textContent: string, data: ArchiveType[], selected?: string, old): void => {
  const label = document.createElement('label')
  label.htmlFor = id
  label.textContent = textContent

  const select = document.createElement('select')
  select.id = id
  select.name = id

  data.forEach((item) => {
    const option = document.createElement('option')
    option.value = item.archive_type
    option.textContent = item.archive_name
    if (selected === item.archive_type) {
      option.selected = true
    }
    select.appendChild(option)
  })

  parent.appendChild(label)
  parent.appendChild(select)

  // 全部作って hidden にする
  const nameLabel = document.createElement('label')
  nameLabel.htmlFor = `${role}[${count}][archive_name]`
  nameLabel.textContent = 'アーカイブ名'
  parent.appendChild(nameLabel)
  const archiveName = document.createElement('input')
  archiveName.id = `${role}[${count}][archive_name]`
  archiveName.name = `${role}[${count}][archive_name]`
  if (old) {
    archiveName.value = old.archive_name
  }
  parent.appendChild(archiveName)

  const videoLabel = document.createElement('label')
  videoLabel.htmlFor = `${role}[${count}][video_url]`
  videoLabel.textContent = '動画URL'
  parent.appendChild(videoLabel)
  const videoUrl = document.createElement('input')
  videoUrl.id = `${role}[${count}][video_url]`
  videoUrl.name = `${role}[${count}][video_url]`
  if (old) {
    videoUrl.value = old.video_url
  }
  parent.appendChild(videoUrl)

  const thumbnailLabel = document.createElement('label')
  thumbnailLabel.htmlFor = `${role}[${count}][thumbnail_url]`
  thumbnailLabel.textContent = 'サムネイルURL'
  parent.appendChild(thumbnailLabel)
  const thumbnailUrl = document.createElement('input')
  thumbnailUrl.id = `${role}[${count}][thumbnail_url]`
  thumbnailUrl.name = `${role}[${count}][thumbnail_url]`
  if (old) {
    thumbnailUrl.value = old.thumbnail_url
  }
  parent.appendChild(thumbnailUrl)

  const postLabel = document.createElement('label')
  postLabel.htmlFor = `${role}[${count}][post_url]`
  postLabel.textContent = '投稿URL'
  parent.appendChild(postLabel)
  const postUrl = document.createElement('input')
  postUrl.id = `${role}[${count}][post_url]`
  postUrl.name = `${role}[${count}][post_url]`
  if (old) {
    postUrl.value = old.post_url
  }
  parent.appendChild(postUrl)

  select.addEventListener('change', (e) => {
    if (e.target.value === '1') {
      nameLabel.hidden = false
      archiveName.hidden = false
      videoLabel.hidden = false
      videoUrl.hidden = false
      thumbnailLabel.hidden = false
      thumbnailUrl.hidden = false

      postLabel.hidden = true
      postUrl.hidden = true
      postUrl.disabled = true
    }
    else if (e.target.value === '2') {
      nameLabel.hidden = false
      archiveName.hidden = false
      postLabel.hidden = false
      postUrl.hidden = false

      videoLabel.hidden = true
      videoUrl.hidden = true
      videoUrl.disabled = true
      thumbnailLabel.hidden = true
      thumbnailUrl.hidden = true
      thumbnailUrl.disabled = true
    }
    else if (e.target.value === '3') {
      nameLabel.hidden = true
      archiveName.hidden = true
      archiveName.disabled = true
      videoLabel.hidden = true
      videoUrl.hidden = true
      videoUrl.disabled = true
      thumbnailLabel.hidden = true
      thumbnailUrl.hidden = true
      thumbnailUrl.disabled = true
      postLabel.hidden = true
      postUrl.hidden = true
      postUrl.disabled = true
    }
  })

  const event = new Event('change', { bubbles: true })
  select.dispatchEvent(event)
}

addLyricistBtn.addEventListener('click', () => add(document.getElementById('lyricists') as HTMLDivElement, 'lyricists'))
addComposerBtn.addEventListener('click', () => add(document.getElementById('composers') as HTMLDivElement, 'composers'))
addArrangerBtn.addEventListener('click', () => add(document.getElementById('arrangers') as HTMLDivElement, 'arrangers'))
addArchiveBtn.addEventListener('click', () => addArchive(document.getElementById('archives') as HTMLDivElement, 'archives'))

interface OldCreator {
  creator_id: string
  order_no: number
}

const oldLyricists = (window.oldLyricists) as OldCreator[]
const oldComposers = (window.oldComposers) as OldCreator[]
const oldArrangers = (window.oldArrangers) as OldCreator[]
const oldArchives = (window.oldArchives)

const restoreCreator = (oldCreators: OldCreator[], role: string): void => {
  oldCreators?.forEach((creator) => {
    const parent = document.getElementById(role) as HTMLDivElement
    const count = parent.querySelectorAll(`.${role}`).length

    const div = document.createElement('div')
    div.className = `${role} ${role}-${count}`

    const [selectLabel, select] = createSelect(`${role}[${count}][creator_id]`, `クリエイター${count + 1}`, creators, creator.creator_id)
    div.appendChild(selectLabel)
    div.appendChild(select)
    const [inputLabel, input] = createInput(`${role}[${count}][order_no]`, `表示順${count + 1}`, `number`, creator.order_no.toString())
    div.appendChild(inputLabel)
    div.appendChild(input)
    div.appendChild(createDeleteButton(parent, role, count))

    parent.appendChild(div)
  })
}

restoreCreator(oldLyricists, 'lyricists')
restoreCreator(oldComposers, 'composers')
restoreCreator(oldArrangers, 'arrangers')

const restoreArchive = (oldArchives): void => {
  oldArchives?.forEach((archive) => {
    const role = 'archives'
    const parent = document.getElementById('archives') as HTMLDivElement
    const count = parent.querySelectorAll(`.${role}`).length

    const div = document.createElement('div')
    div.className = `${role} ${role}-${count}`

    createSelectArchive(div, count, role, `${role}[${count}][archive_type]`, `種別${count + 1}`, archiveTypes, archive.archive_type, archive)
    const [inputDateLabel, inputDate] = createInput(`${role}[${count}][archived_on]`, `日付${count + 1}`, `date`, archive.archived_on)
    div.appendChild(inputDateLabel)
    div.appendChild(inputDate)
    const [inputLabel, input] = createInput(`${role}[${count}][order_no]`, `表示順${count + 1}`, `number`, archive.order_no)
    div.appendChild(inputLabel)
    div.appendChild(input)
    div.appendChild(createDeleteButton(parent, role, count))

    parent.appendChild(div)
  })
}

restoreArchive(oldArchives)
