<?php

/*
| Caregiver skills, badges and quizzes.
| A skill can be: declared (caregiver says so), tested (passed the quiz) or certified (admin checked a certificate).
| Each quiz question: q, options (list), answer (index of the correct option), why (shown after passing).
*/

return [
    'pass_mark' => 8,          // out of 10
    'retry_hours' => 24,       // wait after a failed attempt
    'good_conduct_valid_months' => 6,
    'good_conduct_warn_months' => 5,
    'auto_pause_reporters' => 2, // distinct people with open reports pause a caregiver's listing

    'report_reasons' => [
        'no_show' => "Didn't show up or very unreliable",
        'fake' => 'Fake profile, photo or documents',
        'unsafe' => 'Unsafe, rough or abusive behaviour',
        'theft' => 'Theft or dishonesty',
        'money' => 'Asked for money upfront or a payment problem',
        'other' => 'Something else',
    ],

    'skills' => [
        'elderly_care' => [
            'label' => 'Elderly care',
            'blurb' => 'Daily care, dignity, falls prevention and spotting when an older person is unwell.',
            'quiz' => [
                ['q' => 'An elderly client is suddenly more confused than usual today. What should you do?', 'options' => ['Let them sleep it off', 'Report it to the family or a health worker the same day', 'Give them a painkiller', 'Wait a week to see if it continues'], 'answer' => 1, 'why' => 'Sudden confusion can be a sign of infection (such as a urine infection), dehydration or a stroke, and needs prompt attention.'],
                ['q' => 'Which is the best way to prevent falls at home?', 'options' => ['Keep floors clear, dry and well lit', 'Keep the client in bed all day', 'Use loose rugs for softness', 'Turn off lights to save power at night'], 'answer' => 0, 'why' => 'Clutter, wet floors, loose rugs and poor lighting are common causes of falls.'],
                ['q' => 'An elderly client drinks very little water. What is the main risk?', 'options' => ['Weight gain', 'Dehydration, which can cause confusion and falls', 'Better sleep', 'No risk'], 'answer' => 1, 'why' => 'Older people often feel less thirst; offer drinks regularly through the day.'],
                ['q' => 'Your client falls and says their hip hurts badly. What do you do first?', 'options' => ['Pull them up quickly', 'Keep them still and comfortable, and call for medical help', 'Make them walk to test the hip', 'Give them tea and wait'], 'answer' => 1, 'why' => 'Moving someone with a possible fracture can cause more harm. Keep them warm and still and get help.'],
                ['q' => 'How should you treat an elderly client when bathing them?', 'options' => ['Quickly, without talking', 'Explain what you are doing, keep them covered where possible and let them do what they can', 'Leave the door open for others to help', 'Bathe them in cold water to save time'], 'answer' => 1, 'why' => 'Privacy, dignity and independence are central to good care.'],
                ['q' => 'A client refuses to eat lunch. What is the right response?', 'options' => ['Force-feed them', 'Offer again later or something else they like, and record and report if it continues', 'Skip their meals for the day', 'Tell them off'], 'answer' => 1, 'why' => 'Respect their choice, try alternatives, and report ongoing poor appetite.'],
                ['q' => 'You notice unexplained bruises on an elderly client. What should you do?', 'options' => ['Ignore it', 'Record what you saw and report it to the family or responsible person', 'Post a photo online', 'Ask the neighbours'], 'answer' => 1, 'why' => 'Unexplained injuries can be a sign of falls or abuse and must be reported.'],
                ['q' => 'What helps an older person who uses a walking frame stay safe?', 'options' => ['Hurrying them along', 'Making sure the frame is within reach and they wear well-fitting, non-slip shoes', 'Letting them walk in socks on a tiled floor', 'Taking the frame away so they rest'], 'answer' => 1, 'why' => 'Correct footwear and keeping walking aids within reach reduce falls.'],
                ['q' => 'Which of these is a warning sign you should report?', 'options' => ['Enjoying a radio programme', 'New swelling of the legs or shortness of breath', 'Asking for tea', 'Sleeping after lunch'], 'answer' => 1, 'why' => 'New swelling or breathlessness can point to heart or other problems that need a health worker.'],
                ['q' => 'Why is a daily care record (notebook) useful?', 'options' => ['It is not useful', 'It shows the family and health workers what was eaten, taken and noticed each day', 'It is only for payment', 'It replaces the doctor'], 'answer' => 1, 'why' => 'Good records help spot changes early and keep everyone informed.'],
            ],
        ],

        'dementia_care' => [
            'label' => 'Dementia care',
            'blurb' => 'Calm communication, routines, wandering and agitation.',
            'quiz' => [
                ['q' => 'A client with dementia insists it is 1985 and they must go to work. What is the best response?', 'options' => ['Argue until they accept the date', 'Stay calm, acknowledge their feelings and gently redirect them to an activity', 'Lock them in a room', 'Laugh it off'], 'answer' => 1, 'why' => 'Arguing causes distress; reassurance and redirection work better.'],
                ['q' => 'Why is a regular daily routine helpful for someone with dementia?', 'options' => ['It reduces confusion and anxiety', 'It is not helpful', 'It makes them sleep all day', 'It cures dementia'], 'answer' => 0, 'why' => 'Predictable routines help people with memory problems feel secure.'],
                ['q' => 'How should you speak to a person with dementia?', 'options' => ['Fast, with many questions at once', 'Slowly and clearly, one simple question or step at a time', 'Loudly from another room', 'Only through the family'], 'answer' => 1, 'why' => 'Short, simple sentences and one step at a time are easiest to follow.'],
                ['q' => 'Your client becomes agitated every evening (sundowning). What can help?', 'options' => ['Bright, calm surroundings, a quiet routine and less noise in the evening', 'Loud TV to distract them', 'Extra coffee', 'Leaving them alone in the dark'], 'answer' => 0, 'why' => 'Reducing noise, keeping lighting good and a calm routine can ease evening agitation.'],
                ['q' => 'A client with dementia tends to wander. Which step improves safety?', 'options' => ['Tie them to a chair', 'Make sure they carry ID with a contact phone number and keep exits supervised', 'Give them sleeping pills without a prescription', 'Ignore it'], 'answer' => 1, 'why' => 'ID and supervision help a wandering person get home safely. Restraints are harmful and not allowed.'],
                ['q' => 'A client who cannot explain themselves becomes suddenly aggressive. What should you consider?', 'options' => ['They are just being difficult', 'They may be in pain, hungry, need the toilet or be frightened', 'They need punishment', 'Nothing can be done'], 'answer' => 1, 'why' => 'Changes in behaviour are often a way of communicating an unmet need such as pain.'],
                ['q' => 'What is the best way to offer choices when dressing?', 'options' => ['Show two outfits and let them choose', 'Lay out ten outfits', 'Never give choices', 'Ask what they want to wear tomorrow next week'], 'answer' => 0, 'why' => 'A small number of choices keeps independence without causing confusion.'],
                ['q' => 'The client keeps asking where their late husband is. A kind approach is to:', 'options' => ['Bluntly remind them he died, every time', 'Respond to the feeling, for example "You miss him. Tell me about him."', 'Ignore the question', 'Tell them he is coming tomorrow'], 'answer' => 1, 'why' => 'Repeating bad news causes fresh grief each time; responding to the emotion is kinder.'],
                ['q' => 'Which home change helps a person with dementia?', 'options' => ['Clear labels or pictures on doors such as the toilet', 'Frequently moving the furniture', 'Removing all lights', 'Mirrors everywhere'], 'answer' => 0, 'why' => 'Simple signs help people find their way and stay independent.'],
                ['q' => 'Who should you tell if the client\'s memory or behaviour changes quickly over a few days?', 'options' => ['No one', 'The family and a health worker', 'Only other caregivers on social media', 'The neighbours'], 'answer' => 1, 'why' => 'Quick changes can be caused by infection or other treatable illness.'],
            ],
        ],

        'baby_child_care' => [
            'label' => 'Baby & child care',
            'blurb' => 'Safe sleep, feeding, hygiene and danger signs in babies and young children.',
            'quiz' => [
                ['q' => 'What is the safest position to put a baby down to sleep?', 'options' => ['On their back', 'On their tummy', 'On a pillow', 'Propped up on the sofa'], 'answer' => 0, 'why' => 'Back sleeping on a firm, flat surface reduces the risk of sudden infant death.'],
                ['q' => 'How long is exclusive breastfeeding recommended for?', 'options' => ['2 weeks', 'The first 6 months', '2 years with no other food', 'It is not recommended'], 'answer' => 1, 'why' => 'WHO and the Kenya Ministry of Health recommend exclusive breastfeeding for the first 6 months.'],
                ['q' => 'How should you check bath water for a baby?', 'options' => ['Pour it straight on the baby', 'Test it with your elbow or wrist first; it should feel warm, not hot', 'Use very hot water to kill germs', 'There is no need to check'], 'answer' => 1, 'why' => 'Babies scald easily. Always test water and never leave a baby alone in the bath.'],
                ['q' => 'A baby under 3 months has a fever. What should happen?', 'options' => ['Wait a few days', 'Seek medical care the same day', 'Wrap them in heavy blankets', 'Give adult painkillers'], 'answer' => 1, 'why' => 'Fever in a young baby can be a sign of serious infection.'],
                ['q' => 'A baby keeps crying and you feel very frustrated. What is the right thing to do?', 'options' => ['Shake the baby to stop the crying', 'Put the baby safely in the cot, step away to calm down and call for help', 'Shout at the baby', 'Give the baby alcohol'], 'answer' => 1, 'why' => 'Shaking a baby can cause brain damage or death. Never shake a baby.'],
                ['q' => 'Which is a danger sign in a child that needs urgent care?', 'options' => ['Playing happily', 'Convulsions, not able to drink, or very fast or difficult breathing', 'Asking for a snack', 'A small scratch'], 'answer' => 1, 'why' => 'These are among the general danger signs that need urgent medical attention.'],
                ['q' => 'A child has watery diarrhoea. What helps prevent dehydration?', 'options' => ['Stop all fluids', 'Give ORS (oral rehydration solution) and keep feeding, and seek care if it does not improve', 'Give only soda', 'Give antibiotics from the shop'], 'answer' => 1, 'why' => 'ORS (with zinc as advised by a health worker) and continued feeding are the standard treatment.'],
                ['q' => 'When must you wash your hands when caring for a baby?', 'options' => ['Only at the end of the day', 'Before feeding or preparing food, and after nappy changes or using the toilet', 'Never, babies need germs', 'Only if they look dirty'], 'answer' => 1, 'why' => 'Handwashing at these times prevents the spread of diarrhoea and other infections.'],
                ['q' => 'How should medicines and cleaning products be stored in a home with children?', 'options' => ['On the kitchen floor', 'Up high or locked away, in their original containers', 'In old soda bottles', 'Next to the baby\'s bottles'], 'answer' => 1, 'why' => 'Children are often poisoned by medicines and chemicals stored within reach or in drink bottles.'],
                ['q' => 'How should a newborn\'s umbilical cord stump be cared for?', 'options' => ['Keep it clean and dry, and seek care if it is red, swollen or smelly', 'Cover it with cow dung or ash', 'Pull it off early', 'Keep it wet and covered'], 'answer' => 0, 'why' => 'Clean, dry cord care prevents infection. Never apply traditional substances.'],
            ],
        ],

        'bedridden_care' => [
            'label' => 'Bedridden care',
            'blurb' => 'Turning, pressure sores, safe moving, feeding and hygiene in bed.',
            'quiz' => [
                ['q' => 'How often should a person who cannot move themselves usually be repositioned?', 'options' => ['Once a day', 'At least every 2 hours, or as the care plan says', 'Once a week', 'Only when they ask'], 'answer' => 1, 'why' => 'Regular turning relieves pressure and prevents bed sores.'],
                ['q' => 'Where do pressure sores most often develop?', 'options' => ['Over bony areas like the lower back (sacrum), hips and heels', 'On the stomach', 'On the palms', 'On the chest'], 'answer' => 0, 'why' => 'Bony areas carry the most pressure when lying or sitting.'],
                ['q' => 'You find a red patch on the client\'s heel that does not fade when pressed. What should you do?', 'options' => ['Massage it hard', 'Keep pressure off it, keep the skin clean and dry, and report it', 'Ignore it', 'Cover it with tight tape'], 'answer' => 1, 'why' => 'Redness that does not fade is an early pressure sore. Rubbing it can cause more damage.'],
                ['q' => 'What position should a client be in when eating or drinking in bed?', 'options' => ['Lying flat', 'Sitting upright as much as possible, and staying upright for a while after', 'On their stomach', 'Head lower than feet'], 'answer' => 1, 'why' => 'Sitting up reduces the risk of choking and food going into the lungs.'],
                ['q' => 'When helping move a client, how should you lift?', 'options' => ['Bend your back and lift fast', 'Keep your back straight, bend your knees, and use a second person or aid if needed', 'Pull them by the arms', 'Lift alone however heavy they are'], 'answer' => 1, 'why' => 'Safe moving protects both you and the client. Never pull by the arms.'],
                ['q' => 'Where should a urinary catheter bag be kept?', 'options' => ['Above the level of the bladder', 'Below the level of the bladder, not touching the floor', 'On the pillow', 'Anywhere'], 'answer' => 1, 'why' => 'Keeping it below the bladder stops urine flowing back and reduces infection.'],
                ['q' => 'Why is skin kept clean and dry for a client in diapers?', 'options' => ['It does not matter', 'Wet skin breaks down more easily, causing sores and infection', 'To save diapers', 'Only for the smell'], 'answer' => 1, 'why' => 'Moisture weakens the skin. Change promptly, clean gently and dry well.'],
                ['q' => 'Why is mouth care important for a bedridden client?', 'options' => ['It prevents mouth infections and helps them eat and feel comfortable', 'It is only for appearance', 'It is not needed if they do not eat', 'It replaces drinking water'], 'answer' => 0, 'why' => 'Good mouth care prevents infections and discomfort, even in people who are not eating.'],
                ['q' => 'A client in bed suddenly has a painful, swollen calf. What should you do?', 'options' => ['Massage the leg firmly', 'Do not rub it, and report it urgently to a health worker', 'Apply heat for a week', 'Ignore it'], 'answer' => 1, 'why' => 'This can be a blood clot (DVT). Rubbing it can be dangerous; it needs urgent assessment.'],
                ['q' => 'What helps keep a bedridden client\'s joints and muscles healthy?', 'options' => ['Keeping every limb completely still', 'Gentle movements and exercises as advised by the physiotherapist or nurse', 'Tying the legs straight', 'Heavy weights'], 'answer' => 1, 'why' => 'Prescribed gentle exercises prevent stiffness and weakness.'],
            ],
        ],

        'first_aid' => [
            'label' => 'First aid',
            'blurb' => 'Choking, bleeding, burns, fainting, seizures and CPR basics.',
            'quiz' => [
                ['q' => 'An adult is choking and cannot cough, speak or breathe. What should you do?', 'options' => ['Give them water', 'Give up to 5 back blows, then up to 5 abdominal thrusts, and call for help', 'Wait for it to pass', 'Lay them down to sleep'], 'answer' => 1, 'why' => 'Back blows and abdominal thrusts are the standard first-aid response to severe choking in adults.'],
                ['q' => 'What is the first thing to do for a minor burn?', 'options' => ['Apply butter or oil', 'Cool it under cool running water for about 20 minutes', 'Pop any blisters', 'Apply toothpaste'], 'answer' => 1, 'why' => 'Cool running water reduces damage. Never apply butter, oil or toothpaste.'],
                ['q' => 'A wound is bleeding heavily. What should you do?', 'options' => ['Apply firm, direct pressure with a clean cloth and get help', 'Wash it for 10 minutes first', 'Let it bleed to clean it', 'Put sand on it'], 'answer' => 0, 'why' => 'Direct pressure is the most effective way to control bleeding.'],
                ['q' => 'For adult CPR, what is the ratio of chest compressions to rescue breaths?', 'options' => ['5 compressions to 5 breaths', '30 compressions to 2 breaths', '2 compressions to 30 breaths', '10 compressions to 1 breath'], 'answer' => 1, 'why' => 'The standard ratio is 30:2, at about 100 to 120 compressions a minute.'],
                ['q' => 'Someone is having a seizure (fit). What should you do?', 'options' => ['Put something in their mouth', 'Hold them down firmly', 'Clear the area, cushion their head, time the seizure, and turn them on their side when it stops', 'Pour water on them'], 'answer' => 2, 'why' => 'Never put anything in their mouth or restrain them. Call for help if it lasts more than 5 minutes.'],
                ['q' => 'Which signs suggest a stroke?', 'options' => ['Face drooping, arm weakness, slurred speech: time to call for help', 'Sneezing', 'Hunger', 'Itchy skin'], 'answer' => 0, 'why' => 'Remember FAST: Face, Arms, Speech, Time to get emergency help.'],
                ['q' => 'A person with diabetes is shaky, sweaty and confused but awake and able to swallow. What can you give?', 'options' => ['Nothing', 'A sugary drink or sweets, then tell a health worker', 'Their insulin', 'A large meal of meat'], 'answer' => 1, 'why' => 'These are signs of low blood sugar. Quick sugar helps if they can swallow safely.'],
                ['q' => 'A child may have swallowed a poison. What should you NOT do?', 'options' => ['Call for medical help', 'Keep the container to show the health worker', 'Make the child vomit', 'Stay with the child'], 'answer' => 2, 'why' => 'Making someone vomit can cause more harm. Get medical help and bring the container.'],
                ['q' => 'Someone has a nosebleed. What is the correct first aid?', 'options' => ['Tilt the head back', 'Sit them down, lean them forward and pinch the soft part of the nose for 10 to 15 minutes', 'Lie them flat', 'Blow the nose hard'], 'answer' => 1, 'why' => 'Leaning forward stops blood going down the throat; steady pinching stops the bleeding.'],
                ['q' => 'An unconscious person is breathing normally. How should you position them while waiting for help?', 'options' => ['On their back with a pillow under the head', 'In the recovery position (on their side)', 'Sitting up', 'Standing'], 'answer' => 1, 'why' => 'The recovery position keeps the airway open and lets fluids drain out.'],
            ],
        ],

        'infection_control' => [
            'label' => 'Infection prevention & hygiene',
            'blurb' => 'Handwashing, gloves, soiled linen, sharps and food hygiene.',
            'quiz' => [
                ['q' => 'What is the single most important way to prevent spreading infection?', 'options' => ['Wearing perfume', 'Washing hands properly with soap and water (or using hand rub)', 'Opening windows', 'Taking antibiotics'], 'answer' => 1, 'why' => 'Hand hygiene is the most effective way to stop infections spreading.'],
                ['q' => 'When washing hands with soap, how long should you rub?', 'options' => ['2 seconds', 'At least 20 seconds, covering all parts of the hands', 'Only the palms', 'Just rinse with water'], 'answer' => 1, 'why' => 'Thorough rubbing of all surfaces, including thumbs and fingertips, removes germs.'],
                ['q' => 'Do gloves replace handwashing?', 'options' => ['Yes', 'No. Wash or sanitise your hands before putting gloves on and after taking them off', 'Only on Sundays', 'Only for children'], 'answer' => 1, 'why' => 'Gloves can have tiny holes and hands can get dirty when removing them.'],
                ['q' => 'You finish changing a diaper and next need to prepare food. What should you do?', 'options' => ['Keep the same gloves on', 'Remove gloves, wash hands, then prepare food', 'Wipe gloves on your clothes', 'Prepare food then wash'], 'answer' => 1, 'why' => 'Change gloves and wash hands between dirty and clean tasks.'],
                ['q' => 'How should linen soiled with urine or stool be handled?', 'options' => ['Shake it out in the room', 'Roll it carefully away from your body, wearing gloves, and wash it separately in hot water', 'Hold it against your uniform', 'Leave it on the floor'], 'answer' => 1, 'why' => 'Shaking spreads germs; careful handling and hot washing are safer.'],
                ['q' => 'How should used needles or lancets be disposed of?', 'options' => ['In the normal dustbin', 'In a hard, puncture-proof sharps container, never recapping by hand', 'Flushed down the toilet', 'Kept for reuse'], 'answer' => 1, 'why' => 'This prevents needle-stick injuries and infections.'],
                ['q' => 'You have a cough and fever. What should you do before going to work with a frail client?', 'options' => ['Go as normal', 'Tell the family and agree what to do; cover coughs and wear a mask if you must be there', 'Hide it', 'Take the client\'s medicine'], 'answer' => 1, 'why' => 'Frail clients catch infections easily; be open so the family can plan.'],
                ['q' => 'What is the correct way to cover a cough or sneeze?', 'options' => ['Into your hands, then touch the client', 'Into a tissue or your elbow, then clean your hands', 'Towards the client', 'No need to cover'], 'answer' => 1, 'why' => 'This keeps droplets off hands and surfaces.'],
                ['q' => 'Which surfaces need regular cleaning in a sick person\'s room?', 'options' => ['Only the ceiling', 'Frequently touched surfaces like bed rails, door handles, switches and toilets', 'None', 'Only the windows'], 'answer' => 1, 'why' => 'Germs spread through frequently touched surfaces.'],
                ['q' => 'How should cooked food be kept safe?', 'options' => ['Left uncovered on the counter all day', 'Covered, and kept hot or refrigerated, with raw and cooked foods kept apart', 'Mixed with raw meat', 'Reheated many times'], 'answer' => 1, 'why' => 'Food left out or mixed with raw foods can cause food poisoning.'],
            ],
        ],

        'medication_support' => [
            'label' => 'Medication support',
            'blurb' => 'Giving medicines as prescribed, records, storage and side effects.',
            'quiz' => [
                ['q' => 'Who decides what dose of medicine a client takes?', 'options' => ['The caregiver', 'The prescriber (doctor or clinician), as written on the label or prescription', 'A neighbour', 'The client\'s friends'], 'answer' => 1, 'why' => 'Caregivers support medicines as prescribed and never change doses.'],
                ['q' => 'Before giving a medicine, which checks should you make?', 'options' => ['Right person, right medicine, right dose, right time and right way (e.g. by mouth)', 'Only the colour of the tablet', 'Only the price', 'No checks are needed'], 'answer' => 0, 'why' => 'These "five rights" prevent most medication mistakes.'],
                ['q' => 'The client missed a dose this morning. What should you do?', 'options' => ['Give a double dose at the next time', 'Check the label or ask the pharmacist or doctor what to do', 'Skip all medicines for the day', 'Give whatever is left'], 'answer' => 1, 'why' => 'Doubling up can be dangerous. Follow advice from the pharmacist or prescriber.'],
                ['q' => 'The client struggles to swallow a tablet. Can you crush it?', 'options' => ['Always', 'Only after a pharmacist or doctor says it is safe for that medicine', 'Yes, and mix it into hot tea', 'Throw it away'], 'answer' => 1, 'why' => 'Some medicines are dangerous or don\'t work if crushed.'],
                ['q' => 'Why should you write down each dose given?', 'options' => ['It is not needed', 'So everyone knows what was taken and doses are not missed or doubled', 'Only for payment', 'To show off'], 'answer' => 1, 'why' => 'A medicine record prevents errors and helps health workers.'],
                ['q' => 'After starting a new medicine, the client develops a rash and swollen lips. What should you do?', 'options' => ['Give the next dose anyway', 'Treat it as urgent: get medical help, and don\'t give more until advised', 'Ignore it', 'Apply cooking oil'], 'answer' => 1, 'why' => 'Swelling of the lips or face can be a serious allergic reaction.'],
                ['q' => 'Where should medicines be stored?', 'options' => ['As the label says (e.g. cool and dry), in their original packs, out of children\'s reach', 'In the sun on a window', 'Mixed together in one bottle', 'In the bathroom where it is wet'], 'answer' => 0, 'why' => 'Correct storage keeps medicines safe and working.'],
                ['q' => 'Can you give the client a medicine prescribed for someone else?', 'options' => ['Yes, if the symptoms are similar', 'No, never', 'Yes, half a dose', 'Only at night'], 'answer' => 1, 'why' => 'Medicines are prescribed for a specific person and condition.'],
                ['q' => 'What should you do with expired medicines?', 'options' => ['Use them up first', 'Do not give them; return them to a pharmacy or health facility for safe disposal', 'Give them to a neighbour', 'Throw them in a river'], 'answer' => 1, 'why' => 'Expired medicines may not work or may be harmful.'],
                ['q' => 'A client asks you to stop giving a medicine because they feel better. What do you do?', 'options' => ['Stop it immediately', 'Encourage them to keep taking it as prescribed and tell the family or prescriber', 'Hide the medicine', 'Double the dose'], 'answer' => 1, 'why' => 'Stopping some medicines suddenly is dangerous; changes are for the prescriber to decide.'],
            ],
        ],

        'disability_support' => [
            'label' => 'Disability support',
            'blurb' => 'Respectful support, communication, independence and safe mobility.',
            'quiz' => [
                ['q' => 'What is the best first step before helping a person with a disability?', 'options' => ['Help without asking', 'Ask if they want help and how they prefer to be helped', 'Speak to their companion instead', 'Wait until they fall'], 'answer' => 1, 'why' => 'People know their own needs best; asking respects their independence.'],
                ['q' => 'Before helping someone transfer from a wheelchair, what must you do?', 'options' => ['Put the brakes on and move footrests out of the way', 'Push the chair to keep it moving', 'Remove the cushion', 'Nothing'], 'answer' => 0, 'why' => 'Locked brakes and clear footrests prevent falls.'],
                ['q' => 'How should you communicate with someone who is deaf and lip-reads?', 'options' => ['Shout from behind them', 'Face them in good light and speak clearly at a normal pace', 'Cover your mouth', 'Talk while walking away'], 'answer' => 1, 'why' => 'They need to see your face and lips clearly.'],
                ['q' => 'How do you guide a person who is blind?', 'options' => ['Push them from behind', 'Offer your arm, walk slightly ahead and describe steps and obstacles', 'Grab their cane', 'Leave them to find their way'], 'answer' => 1, 'why' => 'Offering your arm and describing the way is safe and respectful.'],
                ['q' => 'Why should you encourage a client to do tasks they can manage themselves?', 'options' => ['It saves the caregiver work', 'It keeps their skills, strength and dignity', 'It is not encouraged', 'To test them'], 'answer' => 1, 'why' => 'Supporting independence improves wellbeing and keeps abilities longer.'],
                ['q' => 'A client with a disability makes a choice you disagree with but that is safe. What should you do?', 'options' => ['Overrule them', 'Respect their choice', 'Tell everyone', 'Refuse to help'], 'answer' => 1, 'why' => 'Adults with disabilities have the right to make their own choices.'],
                ['q' => 'A wheelchair user sits for long hours. How can you help prevent pressure sores?', 'options' => ['Encourage regular weight shifts or position changes and check the skin', 'Keep them still all day', 'Remove the cushion', 'Nothing can be done'], 'answer' => 0, 'why' => 'Regular pressure relief and skin checks prevent sores.'],
                ['q' => 'Which words are most respectful?', 'options' => ['"The cripple"', '"A person who uses a wheelchair"', '"Wheelchair-bound victim"', '"The handicapped one"'], 'answer' => 1, 'why' => 'Person-first, neutral language is respectful.'],
                ['q' => 'A client with epilepsy has a seizure plan from their doctor. What should you do?', 'options' => ['Ignore it', 'Read it, follow it and know when to call for emergency help', 'Change it yourself', 'Keep it secret from the family'], 'answer' => 1, 'why' => 'The plan tells you exactly what to do for that person.'],
                ['q' => 'A non-verbal client seems upset. How can you find out what is wrong?', 'options' => ['Assume they are being difficult', 'Use their communication method (pictures, signs, gestures) and check for pain, hunger, toileting or discomfort', 'Speak louder', 'Leave them alone for the day'], 'answer' => 1, 'why' => 'Everyone communicates; learn their method and check basic needs.'],
            ],
        ],
    ],
];
