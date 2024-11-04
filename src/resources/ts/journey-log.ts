const fromOn = document.getElementById('from_on') as HTMLInputElement
const toOn = document.getElementById('to_on') as HTMLInputElement

const copyTodayBtn = document.getElementById('copy_today_btn') as HTMLButtonElement
const copyFromToBtn = document.getElementById('copy_from_to_btn') as HTMLButtonElement

const copyToday = (): void => {
  const today = new Date()
  fromOn.value = `${today.getFullYear()}-${(('0' + (today.getMonth() + 1))).slice(-2)}-${('0' + today.getDate()).slice(-2)}`
}

const copyFromTo = (): void => {
  toOn.value = fromOn.value
}

copyTodayBtn.addEventListener('click', copyToday)
copyFromToBtn.addEventListener('click', copyFromTo)

type JourneyLogLinkTypes = JourneyLogLinkType[]

interface JourneyLogLinkType {
  journey_log_link_type_id: string
  journey_log_link_type_name: string
}

const journeyLogLinkTypes = (window.journeyLogLinkTypes) as JourneyLogLinkTypes

const addLinkBtn = document.getElementById('add_link_btn') as HTMLButtonElement
const links = document.getElementById('links') as HTMLDivElement

const createInput = (id: string, textContent: string, type: string, value: string = ''): HTMLDivElement => {
  const div = document.createElement('div')
  div.className = 'form-group'

  const label = document.createElement('label')
  label.htmlFor = id
  label.textContent = textContent

  const groupDiv = document.createElement('div')
  groupDiv.className = 'input-group'

  const input = document.createElement('input')
  input.id = id
  input.name = id
  input.className = `form-control`
  input.type = type
  input.value = value

  groupDiv.appendChild(input)

  div.appendChild(label)
  div.appendChild(groupDiv)

  return div
}

const createSelect = (id: string, textContent: string, data: JourneyLogLinkTypes, selected?: string): HTMLDivElement => {
  const div = document.createElement('div')
  div.className = 'form-group'

  const label = document.createElement('label')
  label.htmlFor = id
  label.textContent = textContent

  const groupDiv = document.createElement('div')
  groupDiv.className = 'input-group'

  const select = document.createElement('select')
  select.id = id
  select.name = id
  select.className = `form-control`

  data.forEach((item) => {
    const option = document.createElement('option')
    option.value = item.journey_log_link_type_id
    option.textContent = item.journey_log_link_type_name
    if (selected === item.journey_log_link_type_id) {
      option.selected = true
    }
    select.appendChild(option)
  })

  groupDiv.appendChild(select)
  div.appendChild(label)
  div.appendChild(groupDiv)

  return div
}

const addLink = (): void => {
  const count = links.querySelectorAll('.link').length

  const div = document.createElement('div')
  div.className = 'link form-group'

  div.appendChild(createInput(`journey_log_links[${count}][journey_log_link_name]`, `リンク名${count + 1}`, `text`))
  div.appendChild(createInput(`journey_log_links[${count}][url]`, `リンクURL${count + 1}`, `text`))
  div.appendChild(createInput(`journey_log_links[${count}][order_no]`, `リンク表示順${count + 1}`, `number`, `0`))
  div.appendChild(createSelect(`journey_log_links[${count}][journey_log_link_type_id]`, `リンク種別${count + 1}`, journeyLogLinkTypes))

  links.appendChild(div)
}

addLinkBtn.addEventListener('click', addLink)

type OldJourneyLogLinks = OldJourneyLogLink[]

interface OldJourneyLogLink {
  journey_log_link_name: string
  journey_log_link_type_id: string
  order_no: number
  url: string
}

const oldJourneyLogLinks = (window.oldJourneyLogLinks) as OldJourneyLogLinks

// あれば復元
oldJourneyLogLinks?.forEach((oldJourneyLogLink) => {
  const count = links.querySelectorAll('.link').length

  const div = document.createElement('div')
  div.className = 'link form-group'

  div.appendChild(createInput(`journey_log_links[${count}][journey_log_link_name]`, `リンク名${count + 1}`, `text`, oldJourneyLogLink.journey_log_link_name))
  div.appendChild(createInput(`journey_log_links[${count}][url]`, `リンクURL${count + 1}`, `text`, oldJourneyLogLink.url))
  div.appendChild(createInput(`journey_log_links[${count}][order_no]`, `リンク表示順${count + 1}`, `number`, `${oldJourneyLogLink.order_no}`))
  div.appendChild(createSelect(`journey_log_links[${count}][journey_log_link_type_id]`, `リンク種別${count + 1}`, journeyLogLinkTypes, oldJourneyLogLink.journey_log_link_type_id))

  links.appendChild(div)
})
