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
